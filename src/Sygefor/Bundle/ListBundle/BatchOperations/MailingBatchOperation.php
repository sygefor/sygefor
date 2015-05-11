<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/04/14
 * Time: 10:41
 */

namespace Sygefor\Bundle\ListBundle\BatchOperations;


use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\ListBundle\BatchOperation\BatchOperationModalConfigInterface;
use Sygefor\Bundle\ListBundle\Entity\Term\PublipostTemplate;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class MailingBatchOperation
 * @package Sygefor\Bundle\TrainingBundle\BatchOperations
 */
class MailingBatchOperation extends AbstractBatchOperation implements BatchOperationModalConfigInterface, ContainerAwareInterface
{
    /**
     * Current template as a filename
     * @var
     */
    private $currentTemplateFileName;

    /** @string current tempalte filename */
    private $currentTemplate;

    /** @var  Container service container */
    private $container;

    /** @var  SecurityContext security Context */
    private $securityContext;

    /** @var  array */
    protected $idList = array();

    /**
     * @param $em
     * @param SecurityContext $securityContext
     * @internal param $path
     */
    public function __construct($em, SecurityContext $securityContext)
    {
        $this->em = $em;

        $this->options['tempDir'] = sys_get_temp_dir().'/sygefor/';
        if (!file_exists($this->options['tempDir'])) {
            mkdir($this->options['tempDir'], 0777);
        }
        $this->securityContext = $securityContext;
    }

    /**
     * make fields available
     * @return mixed
     */
    public function getFields()
    {
        return $this->options['fields'];
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * creates the result file and stores it on disk
     * @param array $idList
     * @param array $options
     * @return mixed
     */
    public function execute(array $idList = array(), array $options = array())
    {
        $this->idList = $idList;
        $entities = $this->getObjectList();
        $deleteTemplate = false;

        //---setting choosed template file
        // 1/ File was provided by user
        if (isset ($this->options['templateFile']) && ($this->options['templateFile'] != '')) {
            $this->options['templateFile']->move($this->options['tempDir'], $this->options['templateFile']->getClientOriginalName());
            $this->currentTemplate = $this->options['tempDir'] . $this->options['templateFile']->getClientOriginalName();
            $this->currentTemplateFileName = $this->options['templateFile']->getClientOriginalName();
            $deleteTemplate = true;
        } else if (isset ($this->options['template']) && (is_integer($this->options['template']))) {
            //file was choosed in template list
            $templateTerm = $this->container->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById('sygefor_list.publipost_template');
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine.orm.entity_manager');
            $repo = $em->getRepository(get_class($templateTerm)) ;
            /** @var PublipostTemplate[] $templates */
            $template = $repo->find($this->options['template']);

            $this->currentTemplate = $template->getAbsolutePath();
            $this->currentTemplateFileName = $template->getFileName();
        } else {// 3/ Error...
            return '';
        }

        $parseInfos = $this->parseFile($this->currentTemplate, $entities);

        if ($deleteTemplate) {
            unlink($this->currentTemplate);
        }

        return $parseInfos ;
    }

    /**
     * Gets a file from module's temp dir if exists, and send it to client.
     * @param $fileName
     * @param null $outputFileName
     * @param array $options
     * @internal param bool $pdf
     * @internal param bool $return
     * @return string|Response
     */
    public function sendFile($fileName, $outputFileName = null, $options = array ('pdf' => false, 'return' => false))
    {
        if (file_exists($this->options['tempDir'] . $fileName)) {

            //security check first : if requested file path doesn't correspond to temp dir,
            //triggering error
            $path_parts = pathinfo($this->options['tempDir'] . $fileName);
            //@todo hm: test this.
            $response = new Response();
            if (realpath($path_parts['dirname']) != $this->options['tempDir']) {
                $response->setContent('Accès non autorisé :'.$path_parts['dirname']);
            }

            // setting output file name
            $outputFileName = (empty($outputFileName)) ? $fileName : $outputFileName ;
            //if pdf file is asked
            if(isset ($options['pdf']) && $options['pdf']) {
                $pdfName = $this->toPdf($fileName);
                $fp = $this->options['tempDir'] . $pdfName;

                //renaming output filename (for end user)
                $tmp = explode(".", $outputFileName);
                $tmp[count($tmp) - 1] = "pdf" ;
                $outputFileName = implode(".", $tmp);
            } else {
                $fp = $this->options['tempDir'] . $fileName;
            }

            if (isset ($options['return']) && $options['return']) {
                $file = new File($fp);
                return $file->move($file->getFileInfo()->getPath(), $outputFileName);
            } else {
                // Set headers
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-type', finfo_file($finfo, $fp));
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $outputFileName . '";');
                $response->headers->set('Content-length', filesize($fp));
                $response->sendHeaders();
                $response->setContent(readfile($fp));
                $response->sendContent();

                //file is then deleted
                unlink($fp);
                return $response;
            }
        }
        return '';
    }

    /**
     * @param $template
     * @param $entities
     * @param bool $getFile
     * @param string $outputFileName
     * @param bool $getPdf
     * @internal param $lines
     * @internal param bool $deleteTemplate
     * @return array
     */
    public function parseFile($template, $entities, $getFile = false, $outputFileName = '', $getPdf = false)
    {
        //getting the file generator
        $TBS = $this->container->get('opentbs');
        $TBS->setOption('noerr', true);

        //loading the template
        $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);

        $lines = array();
        //iterating through properties to construct a (nested) array of properties => values
        foreach ($entities as $entity) {
            if($this->securityContext->isGranted('VIEW', $entity)) {
                $data = $this->container->get('sygefor_list.human_readable_property_accessor_factory')->getAccessor($entity);
                $lines[$entity->getId()] = $data;

            }
        }

        if (!empty($this->idList)){
            $this->reorderByKeys($lines, $this->idList);
        }

        ob_start();

        $alias = $this->container->get('sygefor_list.human_readable_property_accessor_factory')->getEntityAlias($this->targetClass);
        $entityName = ($alias != null) ? $alias : 'entity';

        // merge all fields from the first object
        //fields are merged one by one, so that we dont have to recall a enity name for global names
        if (!empty($lines)) {
//            $vals = current($lines)->toArray();
//            //var_dump($vals);die();
//            foreach ($vals as $fieldName => $prop){
//                $TBS->MergeField($fieldName,$prop);
//            }
            //var_dump(current($lines));
            // @todo : better way
            $TBS->MergeField("global", current($lines)->toArray());
        }

        reset($lines);

        $TBS->MergeBlock($entityName, $lines);

        $error = ob_get_flush();

        if ($error){
            return array('error' => $error);
        }

        $uid = substr(md5(rand()), 0, 5);
        $fileName = $uid . '_' . ( ( !empty($this->currentTemplateFileName) ? $this->currentTemplateFileName : $template->getFileName()));
        $TBS->Show( OPENTBS_FILE, $this->options['tempDir'] . $fileName);
        $TBS->_PlugIns[OPENTBS_PLUGIN]->Close();

        //do we want the file or just infos about it ?
        if ($getFile) {
            return $this->sendFile($fileName, $outputFileName, array( 'pdf' => $getPdf, 'return' => true ) );
        } else {
            // file can then be taken using senFile.
            return array("fileUrl" => $fileName);
        }

    }

    /**
     * @param array $options
     * @return array
     */
    public function getModalConfig($options = array())
    {
        $templateTerm = $this->container->get('sygefor_taxonomy.vocabulary_registry')->getVocabularyById('sygefor_list.publipost_template');
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(get_class($templateTerm)) ;
        /** @var PublipostTemplate[] $templates */
        $templates = $repo->findBy(array('organization'=>$this->securityContext->getToken()->getUser()->getOrganization()));

        $files = array();
        foreach ($templates as $template) {
            $templateEntity = $template->getEntity();
            $ancestor = class_parents($this->targetClass);
            //file is added if its associated entity is an ancestor for current target class
            if ($templateEntity == $this->targetClass || in_array($templateEntity, $ancestor)) {
                $files[] = array('id' => $template->getId(), 'name' =>$template->getName(), 'fileName' => $template->getFileName());
            }
        }

        return array('templateList' => $files);
    }

    /**
     * Getting the list of required objects corresponding to id list
     * @internal param array $idList
     * @return array
     */
    protected function getObjectList()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($this->targetClass, 'e')
            ->where('e.id IN (:ids)')
            ->setParameter('ids',$this->idList);

        return $qb->getQuery()->getResult();
    }


    /**
     * @param $fileName
     * @param null $outputFileName
     * @return string pdf file name, or null if error
     */
    private function toPdf($fileName, $outputFileName = null)
    {
        if (empty($outputFileName)){
            $outputFileName = $fileName;
        }

        //renaming output filename (for end user)
        $info =  pathinfo($outputFileName);
        $outputFileName = $info['filename'] . '.pdf';

        // prepare the process
        $unoconvBin = $this->container->getParameter('unoconv_bin');
        $args = array(
            $unoconvBin,
            "--output=" . $this->options['tempDir'] . $outputFileName,
            $this->options['tempDir'].$fileName
        );
        $pb = new ProcessBuilder($args);
        $process = $pb->getProcess();
        // run
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getCommandLine() . " : " . $process->getErrorOutput());
        }

        return $outputFileName;
    }
}
