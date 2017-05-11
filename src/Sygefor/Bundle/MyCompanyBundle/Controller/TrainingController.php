<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Module;
use Sygefor\Bundle\MyCompanyBundle\Entity\Session;
use Sygefor\Bundle\MyCompanyBundle\Form\ModuleType;
use Sygefor\Bundle\TrainingBundle\Controller\AbstractTrainingController;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Sygefor\Bundle\MyCompanyBundle\SpreadSheet\TrainingBalanceSheet;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * @Route("/training")
 */
class TrainingController extends AbstractTrainingController
{
    protected $sessionClass = Session::class;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param Module $module
     *
     * @Route("/module/{id}/edit", requirements={"id" = "\d+"}, name="module.edit", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("module", class="SygeforMyCompanyBundle:Module", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     *
     * @return array
     */
    public function editModuleAction(Request $request, $module)
    {
        if (!$this->get('security.context')->isGranted('EDIT', $module->getTraining())) {
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
     * @ParamConverter("training", class="SygeforTrainingBundle:Training\AbstractTraining", options={"id" = "id"})
     * @SecureParam(name="training", permissions="VIEW")
     */
    public function balanceSheetAction(AbstractTraining $training)
    {
        $bs = new TrainingBalanceSheet($training, $this->get('phpexcel'), $this->container);

        return $bs->getResponse();
    }
}
