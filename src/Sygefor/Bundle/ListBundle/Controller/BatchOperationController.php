<?php

namespace Sygefor\Bundle\ListBundle\Controller;

use Elastica\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BatchOperationController
 * @package Sygefor\Bundle\ListBundle\Controller
 */
class BatchOperationController extends Controller
{
    /**
     * @Route("/batchoperation/dump", name="sygefor_list.batch.dump")
     * @Template()
     */
    public function dumpAction()
    {
        $operations = $this->get('sygefor_list.batch_operation_registry')->getAll();
        $operations_infos = array ();

        foreach ($operations as $operation) {
            $operations_infos [] = array('label' => $operation->getLabel(), 'id' => $operation->getId(), 'ids' =>1);
        }

        return array('operations' => $operations_infos);
    }


    /**
     * @Route("/batchoperation/{id}/execute", name="sygefor_list.batch_operation.execute", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     */
    public function executeAction($id, Request $request)
    {

        $ids = $request->get('ids');
        $options = $request->get('options');

        //we try to read option list as a JSON string (case of multipart form type)
        if (is_string($options)) {
            $decodeOptions = json_decode($options, $assoc=true) ;
            if (is_array( $decodeOptions )) { //if translation succeeded, the result is stored as options array
                $options = $decodeOptions ;
            }
        }

        //files are stored in option list using form name as key
        foreach ($request->files as $key => $file) {
            $options[$key] = $file;
        }

        //also need to decode id list
        $decodeIds = json_decode($ids, $assoc=true);
        if(is_string($decodeIds)) {
            $ids = $decodeIds ;
        }

        $ids = explode(",", $ids);

        $batchOperation = $this->get('sygefor_list.batch_operation_registry')->get($id);

        if ( !$batchOperation ) {
            throw new NotFoundHttpException('Operation not found : '.$id);
        }

        $options = is_array($options) ? $options : array();
        $batchOperation->setOptions($options);
        return $batchOperation->execute($ids, $options);

    }

    /**
     * @Route("/batchoperation/modalconfig/{service}", name="sygefor_list.batch_operation.modal_config", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     */
    public function modalConfigAction($service, Request $request)
    {
        $options = $request->get('options');

        //we try to read option list as a JSON string (case of multipart form type)
        if (is_string($options)) {
            $decodeOptions = json_decode($options, $assoc=true) ;
            if (is_array( $decodeOptions )) { //if translation succeeded, the result is stored as options array
                $options = $decodeOptions ;
            }
        }

        $batchOperation = $this->get('sygefor_list.batch_operation_registry')->get($service);

        if (method_exists($batchOperation,'getModalConfig')) {
            return $batchOperation->getModalConfig($options);
        }

        return array();
    }

    /**
     * sends file
     * @Route("/batchoperation/{service}/get/{file}/as/{filename}", name="sygefor_list.batch_operation.get_file", options={"expose"=true}, defaults={"_format" = "json", "filename"=null})
     * @Rest\View
     */
    public function fileDownloadAction($service, $file, $filename = null, Request $request)
    {
        $pdf = ( $request->get('pdf') == 'true' ) ? true : false;
        $batchOperation = $this->get('sygefor_list.batch_operation_registry')->get($service);

        if (method_exists($batchOperation,'sendFile')) {
            return $batchOperation->sendFile($file, $filename ? $filename : 'publipostage.odt', array ( 'pdf' => $pdf ));
        }

        return array();
    }


}
