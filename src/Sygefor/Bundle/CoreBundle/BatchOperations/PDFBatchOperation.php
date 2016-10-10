<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/04/14
 * Time: 16:56.
 */
namespace Sygefor\Bundle\CoreBundle\BatchOperations;

use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Knp\Snappy\Pdf;
use Sygefor\Bundle\CoreBundle\BatchOperation\AbstractBatchOperation;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class PDFBatchOperation.
 */
class PDFBatchOperation extends AbstractBatchOperation
{
    /**
     * @var Pdf
     */
    protected $pdf;

    /**
     * @var string
     */
    protected $entityKey;

    /**
     * @var string
     */
    protected $templating;

    /**
     * @var string
     */
    protected $defaultTemplate;

    /**
     * @var string
     */
    protected $templates;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $templateDiscriminator;

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /** @var Kernel */
    protected $kernel;

    /**
     * PDFBatchOperation constructor.
     *
     * @param LoggableGenerator $pdf
     * @param EngineInterface $templating
     * @param SecurityContext $securityContext
     * @param Kernel $kernel
     */
    public function __construct(LoggableGenerator $pdf, EngineInterface $templating, SecurityContext $securityContext, Kernel $kernel)
    {
        $this->pdf = $pdf;
        $this->templating = $templating;
        $this->securityContext = $securityContext;
        $this->kernel = $kernel;
    }

    /**
     * @param string $entityKey
     */
    public function setEntityKey($entityKey)
    {
        $this->entityKey = $entityKey;
    }

    /**
     * @param string $defaultTemplate
     */
    public function setDefaultTemplate($defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * @param string $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @param string $templateDiscriminator
     */
    public function setTemplateDiscriminator($templateDiscriminator)
    {
        $this->templateDiscriminator = $templateDiscriminator;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param array $idList
     * @param array $options
     *
     * @return mixed
     */
    public function execute(array $idList = array(), array $options = array())
    {
        $repository = $this->em->getRepository($this->targetClass);
        $accessor = PropertyAccess::createPropertyAccessor();

        $entities = $this->getObjectList($idList);
        $pages = array();
        foreach ($entities as $entity) {
            // security check
            if ($this->securityContext->isGranted('VIEW', $entity)) {
                // determine the template
                $template = $this->defaultTemplate;
                if ($this->templateDiscriminator) {
                    $key = $accessor->getValue($entity, $this->templateDiscriminator);
                    if (isset($this->templates[$key])) {
                        $template = $this->templates[$key];
                    }
                }

                $signature = null;
                $training = null;
                if ($entity instanceof Training) {
                    $training = $entity;
                }
                else if ($entity instanceof Session) {
                    $training = $entity->getTraining();
                }
                else if ($entity instanceof Inscription) {
                    $training = $entity->getSession()->getTraining();
                }
                //checking signature file existence
                $fs = new Filesystem();
                if ($fs->exists($this->kernel->getRootDir() . '/../web/img/organization/' . $training->getOrganization()->getCode() . '/signature.png')) {
                    $signature = '/img/organization/' . $training->getOrganization()->getCode() . '/signature.png';
                }

                // render the page
                $vars = array();
                $vars[$this->entityKey] = $entity;
                $vars['link'] = $_SERVER['DOCUMENT_ROOT'];
                //prevent escaping quotes in rendered template.
                $vars['autoescape'] = false;
                $vars['signature'] = $signature;
                $pages[$entity->getId()] = $this->templating->render($template, $vars);
            }
        }

        // reorder
        $this->reorderByKeys($pages, $idList);

        // add a page break between each page
        $html = implode('<div style="page-break-after: always;"></div>', $pages);
        $filename = $this->filename ? $this->filename : 'file.pdf';

        // return the pdf
        return new Response(
            $this->pdf->getOutputFromHtml($html, array('print-media-type' => null)),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            )
        );
    }
}
