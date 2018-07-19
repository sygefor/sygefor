<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 10/07/14
 * Time: 15:23.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Session\Session;
use AppBundle\Entity\Material\FileMaterial;
use AppBundle\Entity\Material\LinkMaterial;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Form\Type\Material\FileMaterialType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sygefor\Bundle\CoreBundle\Controller\AbstractMaterialController;

/**
 * @Route("/material")
 */
class MaterialController extends AbstractMaterialController
{
    /**
     * @Route("/{entity_id}/add/{entity_type}/{material_type}/{isPublic}", name="material.add", options={"expose"=true}, defaults={"_format" = "json", "isPublic": false})
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity_id
     * @param $entity_type
     * @param $material_type
     * @param bool $isPublic
     *
     * @return array
     *
     * @throws
     */
    public function addAction(Request $request, $entity_id, $entity_type, $material_type, $isPublic = false)
    {
        $entity = $this->getEntity($entity_id, $entity_type);
        $setEntityMethod = $entity instanceof Session ? 'setSession' : 'setTraining';

        if ($material_type === 'file') {
            return $this->addFileMaterial($request, $entity, $setEntityMethod, $isPublic);
        } elseif ($material_type === 'link') {
            return $this->addLinkMaterial($request, $entity, $setEntityMethod, $isPublic);
        }

        throw new BadRequestHttpException();
    }

    /**
     * @param Request $request
     * @param $entity
     * @param $setEntityMethod
     * @param $isPublic
     *
     * @return array
     */
    protected function addFileMaterial(Request $request, $entity, $setEntityMethod, $isPublic)
    {
        $form = $this->createForm(FileMaterialType::class);
        $form->handleRequest($request);
        if ($request->getMethod() === 'POST') {
            if ($request->files->count() !== 0) {
                $materials = array();
                foreach ($request->files as $file) {
                    //we have to test it in another
                    if ($file[0]->getSize() <= FileMaterial::getMaxFileSize()) {
                        $material = new FileMaterial($isPublic);
                        $entity->addMaterial($material);
                        $material->$setEntityMethod($entity);
                        $material->setFile($file[0]);

                        $em = $this->getDoctrine()->getManager();

                        //persisting material calls move method on file, that can throw an exception if file size limit
                        //is too small in server config
                        try {
                            $em->persist($material);
                        } catch (FileException $e) {
                            return array('error' => $e->getMessage());
                        }
                        $em->flush();
                        $materials[] = $material;
                    } else {
                        return array('error' => 'Le fichier '.$file[0]->getClientOriginalName().' est trop volumineux');
                    }
                }

                return array('materials' => $materials);
            } else {
                //files could be stripped by web server (eg by php.ini's limitations) : we can't get any infos about it
                return array('error' => "Le fichier n'a pu être téléchargé");
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @param Request $request
     * @param $entity
     * @param $setEntityMethod
     * @param $isPublic
     *
     * @return array
     */
    protected function addLinkMaterial(Request $request, $entity, $setEntityMethod, $isPublic)
    {
        $material = new LinkMaterial($isPublic);
        $entity->addMaterial($material);
        $material->$setEntityMethod($entity);

        $form = $this->createFormBuilder($material)
            ->add('name', 'text', array('label' => 'Nom', 'required' => 'true'))
            ->add('url', 'text', array('label' => 'Lien'))
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($material);
                $em->flush();

                return array('material' => $material);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{id}/get/", name="material.get", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View
     * @ParamConverter("material", class="SygeforCoreBundle:AbstractMaterial", options={"id" = "id"})
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     */
    public function getAction($material)
    {
        return parent::getAction($material);
    }
}
