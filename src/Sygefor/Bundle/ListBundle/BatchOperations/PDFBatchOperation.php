<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 07/04/14
 * Time: 16:56
 */

namespace Sygefor\Bundle\ListBundle\BatchOperations;


use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Knp\Snappy\Pdf;
use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\ListBundle\BatchOperation\BatchOperationInterface;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Templating\EngineInterface;
use Volcanus\Csv\Writer;

/**
 * Class PDFBatchOperation
 * @package Sygefor\Bundle\ListBundle\BatchOperations
 */
class PDFBatchOperation extends AbstractBatchOperation
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Pdf
     */
    protected $pdf;

    /**
     * @var strong
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
     * @param EntityManager $em
     * @param LoggableGenerator $pdf
     */
    public function __construct(EntityManager $em, LoggableGenerator $pdf, EngineInterface $templating, SecurityContext $securityContext, Kernel $kernel) {
        $this->em = $em;
        $this->pdf = $pdf;
        $this->templating = $templating;
        $this->securityContext = $securityContext;
        $this->kernel = $kernel;
    }

    /**
     * @param string $entityKey
     */
    public function setEntityKey($entityKey) {
        $this->entityKey = $entityKey;
    }

    /**
     * @param string $defaultTemplate
     */
    public function setDefaultTemplate($defaultTemplate) {
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * @param string $templates
     */
    public function setTemplates($templates) {
        $this->templates = $templates;
    }

    /**
     * @param string $templateDiscriminator
     */
    public function setTemplateDiscriminator($templateDiscriminator) {
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
     * @return mixed
     */
    public function execute(array $idList = array(), array $options = array())
    {
        $repository = $this->em->getRepository($this->targetClass);
        $accessor = PropertyAccess::createPropertyAccessor();

        // get the qb
        $qb = $repository->createQueryBuilder("e");
        $qb->add('where', $qb->expr()->in('e.id', ':ids'));
        $qb->setParameter('ids', $idList);

        // render the result
        $results = $qb->getQuery()->getResult();
        $pages = array();
        foreach($results as $entity) {
            // security check
            if($this->securityContext->isGranted('VIEW', $entity)) {
                // determine the template
                $template = $this->defaultTemplate;
                if($this->templateDiscriminator) {
                    $key = $accessor->getValue($entity, $this->templateDiscriminator);
                    if(isset($this->templates[$key])) {
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
                if($fs->exists($this->kernel->getRootDir() . '/../web/img/urfist/'.$training->getOrganization()->getCode().'/signature.png' )) {
                    $signature = '/img/urfist/'.$training->getOrganization()->getCode().'/signature.png';
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
        $html = join('<div style="page-break-after: always;"></div>', $pages);
        $filename = $this->filename ? $this->filename : 'file.pdf';

        // return the pdf
        return new Response(
            $this->pdf->getOutputFromHtml($html, array("print-media-type" => null)),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
            )
        );
    }
}
