<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Session\Session;
use AppBundle\Entity\Training\Module;
use AppBundle\Form\Type\Training\ModuleType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use AppBundle\Utils\TrainingBalanceSheet;
use Sygefor\Bundle\CoreBundle\Controller\AbstractTrainingController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Route("/training")
 */
class TrainingController extends AbstractTrainingController
{
    protected $sessionClass = Session::class;

    /**
     * This action attach a form to the return array when the user has the permission to edit the training.
     *
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="training.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="training", permissions="VIEW")
     * @ParamConverter("training", class="SygeforCoreBundle:AbstractTraining", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     * @SatisfiesParentSecurityPolicy
     */
    public function viewAction(Request $request, AbstractTraining $training)
    {
        if (!$this->get('security.context')->isGranted('EDIT', $training)) {
            if ($this->get('security.context')->isGranted('VIEW', $training)) {
                return array('training' => $training);
            }

            throw new AccessDeniedException('Action non autorisée');
        }

        $formClass = $training::getFormType();
        $form = $this->createForm(new $formClass($this->get('sygefor_core.access_right_registry')), $training);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'training' => $training);
    }

    /**
     * @Route("module/{id}/edit", requirements={"id" = "\d+"}, name="module.edit", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("module", class="AppBundle:Training\Module", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function editModuleAction(Request $request, Module $module)
    {
        if ( ! $this->get('security.context')->isGranted('EDIT', $module->getTraining())) {
            throw new AccessDeniedException('Action non autorisée');
        }

        $form = $this->createForm(new ModuleType(), $module);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return array('form' => $form->createView(), 'modules' => $module->getTraining()->getModules());
    }

    /**
     * @Route("/duplicate/{id}/{type}", name="training.duplicate", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("training", class="SygeforCoreBundle:AbstractTraining", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function duplicateAction(Request $request, AbstractTraining $training, $type)
    {
        //training can't be created if user has no rights for it
        if (!$this->get('security.context')->isGranted('CREATE', $training)) {
            throw new AccessDeniedException('Action non autorisée');
        }

        /** @var AbstractTraining $cloned */
        $cloned = null;
        // get targetted training type
        $typeClass = $this->get('sygefor_core.registry.training_type')->getType($type);
        if ($type === $training->getType()) {
            $cloned = clone $training;
        } else {
            $cloned = new $typeClass['class']();
            $cloned->copyProperties($training);
        }

        $formClass = $typeClass['class']::getFormType();
        $form = $this->createForm(new $formClass($this->get('sygefor_core.access_right_registry')), $cloned);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // if meeting assign cloned training to the session
                if ($cloned->getType() === 'meeting') {
                    $cloned->getSession()->setTraining($cloned);
                }
                $this->mergeArrayCollectionsAndFlush($cloned, $training);

                $em = $this->getDoctrine()->getManager();
                $em->persist($cloned);
                $em->flush();

                return array('form' => $form->createView(), 'training' => $cloned);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @param AbstractTraining $dest
     * @param AbstractTraining $source
     */
    protected function mergeArrayCollectionsAndFlush($dest, $source)
    {
        $em = $this->getDoctrine()->getManager();

        // clone common arrayCollections
        if (method_exists($source, 'getTags')) {
            $dest->duplicateArrayCollection('addTag', $source->getTags());
        }

        // clone duplicate materials
        $tmpMaterials = $source->getMaterials();
        if (!empty($tmpMaterials)) {
            foreach ($tmpMaterials as $material) {
                $newMat = clone $material;
                $dest->addMaterial($newMat);
            }
        }

        $em->persist($dest);
        $em->flush();
    }

    /**
     * @Route("/{id}/bilan.{_format}", requirements={"id" = "\d+"}, name="training.balancesheet", options={"expose"=true}, defaults={"_format" = "xls"}, requirements={"_format"="csv|xls|xlsx"})
     * @Method("GET")
     * @ParamConverter("training", class="SygeforCoreBundle:AbstractTraining", options={"id" = "id"})
     * @SecureParam(name="training", permissions="VIEW")
     */
    public function balanceSheetAction(AbstractTraining $training)
    {
        $bs = new TrainingBalanceSheet($training, $this->get('phpexcel'), $this->container);

        return $bs->getResponse();
    }
}
