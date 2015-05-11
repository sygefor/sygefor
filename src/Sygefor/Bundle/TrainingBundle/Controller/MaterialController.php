<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/07/14
 * Time: 15:23
 */

namespace Sygefor\Bundle\TrainingBundle\Controller;

use Sygefor\Bundle\TrainingBundle\Entity\FileMaterial;
use Sygefor\Bundle\TrainingBundle\Entity\LinkMaterial;
use Sygefor\Bundle\TrainingBundle\Entity\Material;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\SecureParam;


/**
 * @Route("/training/material/")
 */
class MaterialController extends Controller {

    /**
     * @Route("{training_id}/add/{type}", name="material.add", options={"expose"=true}, defaults={"_format" = "json", "type"="file"})
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @SecureParam(name="training", permissions="EDIT")
     * @ParamConverter("training", class="SygeforTrainingBundle:Training", options={"id" = "training_id"})
     */
    public function addAction($training, $type, Request $request)
    {
        // a file is sent : creating a file material
        if($type == "file") {
            $material = new FileMaterial();
            $material->setTraining($training);
            $form = $this->createForm('material', $material);

            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                $fileInfos = array();
                if ($request->files->count() != 0 ){

                    foreach ($request->files as $file) {
                        //we have to test it in another
                        if ($file[0]->getSize() <= FileMaterial::getMaxFileSize()) {
                            $material = new FileMaterial();

                            $material->setTraining($training);
                            $material->setFile($file[0]);

                            $em = $this->getDoctrine()->getManager();

                            //persisting material calls move method on file, that can throw an exception if file size limit
                            //is too small in server config
                            try {
                                $em->persist($material);
                            } catch (FileException $e) {
                                return array('error' => "Le fichier n'a pu être téléchargé");
                            }
                            $em->flush();
                            $fileInfos[] = array('id' => $material->getId(), 'name' => $material->getFileName());
                        } else {
                            $fileInfos[] = array('error' => "fichier trop volumineux", 'name' => $file[0]->getClientOriginalName());
                        }
                    }
                    return array('material' => $fileInfos);
                } else {//files could be stripped by web server (eg by php.ini's limitations) : we can't get any infos about it
                    return array('error' => "Le fichier n'a pu être téléchargé");
                }
            }
        } else if($type=="link") { // no file sent : a link material is sent
            $material = new LinkMaterial();
            $material->setTraining($training);
            $form = $this->createFormBuilder($material)
                ->add('name', 'text', array("label" => "Nom", "required" => "true"))
                ->add('url', 'text', array("label" => "Lien"))
                ->getForm();

            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $material->setTraining($training);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($material);
                    $em->flush();
                    return array('material' => $material);
                }
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("{id}/remove/", name="material.remove", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     * @ParamConverter("material", class="SygeforTrainingBundle:Material", options={"id" = "id"})
     */
    public function deleteAction($material)
    {
        if($this->get("security.context")->isGranted('EDIT', $material->getTraining())) {
            /** @var $em */
            $em = $this->getDoctrine()->getManager();
            try {
                $em->remove($material);
                $em->flush();
            }catch (Exception $e) {
                return array("error" => $e->getMessage());
            }
            return array();
        } else {
            throw new AccessDeniedException('Accès non autorisé');
        }
    }

    /**
     * @Route("{id}/get/", name="material.get", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     * @ParamConverter("material", class="SygeforTrainingBundle:Material", options={"id" = "id"})
     */
    public function getAction($material)
    {
        if($this->get("security.context")->isGranted('VIEW', $material->getTraining())) {

            if ($material->getType() == "file"){
                return $material->send();
            }
            else if ($material->getType() == "link") {
                return $material->getUrl();
            }

        } else {
            throw new AccessDeniedException('Accès non autorisé');
        }
    }

}
