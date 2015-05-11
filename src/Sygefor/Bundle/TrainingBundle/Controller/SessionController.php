<?php
namespace Sygefor\Bundle\TrainingBundle\Controller;

use Sygefor\Bundle\TrainingBundle\SpreadSheet\EvaluationSheet;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Sygefor\Bundle\TrainingBundle\Form\SessionType;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/training/session")
 */
class SessionController extends Controller
{
    /**
     * @Route("/search", name="session.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_training.session.search');
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
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="session.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="VIEW")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Session $session, Request $request)
    {
        $return = array("session" => $session);
        if($this->get("security.context")->isGranted('EDIT', $session)) {
            $form = $this->createForm(new SessionType(), $session);
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($session);
                    $em->flush();
                    return $return;
                }
            }
            $return['form'] = $form->createView();
        }
        return $return;
    }

    /**
     * @Route("/create/{training}", requirements={"id" = "\d+"}, name="session.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="training", permissions="EDIT")
     * @ParamConverter("training", class="SygeforTrainingBundle:Training", options={"id" = "training"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Training $training, Request $request)
    {
        $session = new Session();
        //$session->setPublished(true);
        $session->setTraining($training);
        $form = $this->createForm(new SessionType(), $session);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($session);
                $em->flush();
                return array('session' => $session);
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/duplicate/{id}", requirements={"id" = "\d+"}, name="session.duplicate", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("session", class="SygeforTrainingBundle:Session")
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function duplicateAction(Session $session, Request $request)
    {
        //training can't be created if user has no rights for it
        if (!$this->get("security.context")->isGranted('EDIT', $session->getTraining())) {
            throw new AccessDeniedException("Action non autorisée");
        }

        $cloned = clone $session;
        $form = $this->createFormBuilder($cloned)
            ->add('dateBegin', 'date', array(
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de début',
                'attr' => array(
                    'placeholder' => 'Date de début'
                )
            ))
            ->add('dateEnd', 'date', array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de fin',
                'attr' => array(
                    'placeholder' => 'Date de fin'
                )
            ))->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cloned);
                $em->flush();

                foreach ($session->getTrainers() as $trainer) {
                    $cloned->addTrainer($trainer);
                }
                $em->persist($cloned);
                $em->flush();
                return array('session' => $cloned);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{id}/remove", requirements={"id" = "\d+"}, name="session.remove", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "id"})
     * @SecureParam(name="session", permissions="DELETE")
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function removeAction(Session $session)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($session);
        $em->flush();
        $this->get('fos_elastica.index')->refresh();

        return $this->redirect($this->generateUrl('session.search'));

    }

    /**
     * @Route("/{id}/evaluations", requirements={"id" = "\d+"}, name="session.evaluations", options={"expose"=true}, defaults={"_format" = "xls"}, requirements={"_format"="csv|xls|xlsx"})
     * @Method("GET")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "id"})
     * @SecureParam(name="session", permissions="DELETE")
     */
    public function evaluationExportAction(Session $session)
    {
        $es = new EvaluationSheet($session, $this->get('phpexcel'),$this->container);

        return $es->getResponse();
    }
}
