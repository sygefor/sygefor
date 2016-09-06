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
use Sygefor\Bundle\ListBundle\BatchOperation\AbstractBatchOperation;
use Sygefor\Bundle\ListBundle\BatchOperation\BatchOperationInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\SecurityContext;
use Volcanus\Csv\Writer;

/**
 * Class CSVBatchOperation
 * @package Sygefor\Bundle\ListBundle\BatchOperations
 */
class CSVBatchOperation extends AbstractBatchOperation
{
    /**
     * @var EntityManager $em
     */
    protected $em;


    /**
     * @var EntityManager $securityContext
     */
    protected $securityContext;

    /**
     * @var array $options
     */
    protected $options = array (
        'volcanus_config'=> array(
            'delimiter'        => ';',
            'enclose'          => true,
            'enclosure'        => '"',
            'escape'           => '"',
            'inputEncoding'    => 'UTF-8',
            'outputEncoding'   => 'ISO-8859-1',
            'writeHeaderLine'  => true,
            'responseFilename' => 'export.csv',
        )

    );

    /**
     * @param EntityManager $em
     * @param SecurityContext $securityContext
     */
    public function __construct(EntityManager $em, SecurityContext $securityContext)
    {
        $this->em = $em ;
        $this->securityContext = $securityContext;
        $this->options['tempDir'] = sys_get_temp_dir().'/sygefor/';
        if (!file_exists($this->options['tempDir'])) {
            mkdir($this->options['tempDir'], 0777);
        }
    }

    /**
     * @param array $idList
     * @return array
     */
    protected function getObjectList(array $idList = array())
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($this->targetClass, 'e')
            ->where('e.id IN (:ids)')
            ->setParameter('ids',$idList);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $idList
     * @param array $options
     * @return mixed
     */
    public function execute(array $idList = array(), array $options =array())
    {
        $entities = $this->getObjectList($idList);

        // accessor
        $accessor = PropertyAccess::createPropertyAccessor();

        // lines
        $lines = array();
        foreach($entities as $entity) {
            if($this->securityContext->isGranted('VIEW', $entity)) {
                $data = array();
                foreach($this->options['fields'] as $key=>$value) {
                    try {
                        $rvalue = $accessor->getValue($entity, $key);

                        if (!empty ($value['type']) && $value['type'] == 'date') {
                            if ($rvalue) {
                                $rvalue = $rvalue->format('d/m/Y');
                            }
                        }
                        $data[$key] = ($rvalue) ? $rvalue : '';
                    } catch(UnexpectedTypeException $e) { }
                }
                $lines[$entity->getId()] = $data;
            }
        }

        // reorder
        $this->reorderByKeys($lines, $idList);

        // fields
        $fields = array();

        foreach($this->options['fields'] as $label => $value) {
            $fields[] = array($label,$value['label']);
        }

        //setting filename
        if (!empty($this->options['filename'])) {
            $this->options['volcanus_config']['responseFilename'] = $this->options['filename'];
        }

        $uid = substr(md5(rand()), 0, 5);
        $fileName = $uid . '_' . $this->options['volcanus_config']['responseFilename'];

        $writer = new Writer($this->options['volcanus_config']);
        $writer->fields($fields);
        $file = new \SplFileObject($this->options['tempDir'].$fileName, 'w+');
        $writer->setFile($file);
        $writer->write($lines);
        //$writer->send();
        return array("fileUrl" => $fileName);
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
    public function sendFile($fileName, $outputFileName = null, $options = array())
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
            $outputFileName = $this->options['volcanus_config']['responseFilename'];
            //if pdf file is asked

            $fp = $this->options['tempDir'] . $fileName;

            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $outputFileName . '";');
            $response->headers->set('Content-length', filesize($fp));
            $response->sendHeaders();
            $response->setContent(readfile($fp));
            $response->sendContent();

            //file is then deleted
            unlink($fp);
            return $response;
        }
        return $this->options['tempDir'] . $fileName;
    }

}
