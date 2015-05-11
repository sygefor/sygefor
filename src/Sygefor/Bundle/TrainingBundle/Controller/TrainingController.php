<?php
namespace Sygefor\Bundle\TrainingBundle\Controller;

use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sygefor\Bundle\TrainingBundle\SpreadSheet\TrainingBalanceSheet;
use Sygefor\Bundle\TrainingBundle\Entity\DiverseTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Internship;
use Sygefor\Bundle\TrainingBundle\Entity\Meeting;
use Sygefor\Bundle\TrainingBundle\Entity\SingleSessionTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\TrainingCourse;
use Sygefor\Bundle\TrainingBundle\Form\DiverseTrainingType;
use Sygefor\Bundle\TrainingBundle\Form\InternshipType;
use Sygefor\Bundle\TrainingBundle\Form\MeetingType;
use Sygefor\Bundle\TrainingBundle\Form\SessionType;
use Sygefor\Bundle\TrainingBundle\Form\TrainingCourseType;
use Sygefor\Bundle\TrainingBundle\Form\TrainingType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 * @Route("/training")
 */
class TrainingController extends Controller
{
    /**
     * @Route("/search", name="training.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_training.semestered.search');
        $search->handleRequest($request);

        // security check
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_training.rights.training.all.view")) {
            $search->addTermFilter("training.organization.id", $this->getUser()->getOrganization()->getId());
        }

        return $search->search();
    }

    /**
     * This action attach a form to the return array when the user has the permission to edit the training
     *
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="training.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="training", permissions="VIEW")
     * @ParamConverter("training", class="SygeforTrainingBundle:Training", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Training $training, Request $request)
    {
        $return = array("training" => $training);
        if($this->get("security.context")->isGranted('EDIT', $training)) {
            $form = $this->createForm($training::getFormType(), $training);
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                }
            }
	        $return['form'] = $form->createView();
        }

        // if the training is single session, add 'session' to the serialization groups
        if($training instanceof SingleSessionTraining) {
            $view = new View($return);
            $view->setSerializationContext(SerializationContext::create()->setGroups(array('Default', 'training', 'session')));
            return $view;
        }

        return $return;
    }

    /**
     * @Route("/create/{type}", name="training.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction($type, Request $request)
    {
        $registry = $this->get("sygefor_training.type.registry");
        $type = $registry->getType($type);

        $class = $type['class'];
        $training = new $class();
        $training->setOrganization($this->getUser()->getOrganization());

        //training can't be created if user has no rights for it
        if (!$this->get("security.context")->isGranted('CREATE', $training)) {
            throw new AccessDeniedException("Action non autorisée");
        }

        $form = $this->createForm($training::getFormType(), $training);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($training);
                $em->flush();
                return array('training' => $training);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/duplicate/{id}", name="training.duplicate", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("training", class="SygeforTrainingBundle:Training")
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function duplicateAction(Training $training, Request $request)
    {
        //training can't be created if user has no rights for it
        if (!$this->get("security.context")->isGranted('CREATE', $training)) {
            throw new AccessDeniedException("Action non autorisée");
        }

        $cloned = clone $training;
        $form = $this->createFormBuilder( $cloned)
            ->add('firstSessionPeriodSemester', 'choice', array(
            'required' => true,
            'choices' => array('1' => '1er semestre', '2' => '2nd semestre'),
            'label' => '1ère session'
        ))
            ->add('firstSessionPeriodYear', null, array(
                'required' => true,
                'label' => 'Année',
            ))->getForm();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                //we can now duplicate materials
                $tmpMaterials = $training->getMaterials();
                if (!empty($tmpMaterials)) {
                    foreach ($tmpMaterials as $material) {
                        $newMat = clone $material;
                        $cloned->addMaterial($newMat);
                    }
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($cloned);
                $em->flush();

                return array('form'=>$form->createView(),'training'=>$cloned);
            }
        }
        return array('form'=>$form->createView());
    }

    /**
     * @Route("/{id}/remove", requirements={"id" = "\d+"}, name="training.remove", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @ParamConverter("training", class="SygeforTrainingBundle:Training", options={"id" = "id"})
     * @SecureParam(name="training", permissions="DELETE")
     * @Rest\View(serializerGroups={"Default", "training"}, serializerEnableMaxDepthChecks=true)
     */
    public function removeAction(Training $training)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($training);
        $em->flush();
        $this->get('fos_elastica.index')->refresh();
        return $this->redirect($this->generateUrl('training.search'));
    }

    /**
     * @Route("/{id}/bilan.{_format}", requirements={"id" = "\d+"}, name="training.balancesheet", options={"expose"=true}, defaults={"_format" = "xls"}, requirements={"_format"="csv|xls|xlsx"})
     * @Method("GET")
     * @ParamConverter("training", class="SygeforTrainingBundle:Training", options={"id" = "id"})
     * @SecureParam(name="training", permissions="VIEW")

     */
    public function balanceSheetAction(Training $training)
    {
        $bs = new TrainingBalanceSheet($training, $this->get('phpexcel'),$this->container);

        return $bs->getResponse();
    }

}
