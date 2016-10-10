<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 09/12/2015
 * Time: 16:26.
 */
namespace Sygefor\Bundle\TrainingBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractParticipation;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ParticipationController.
 *
 * @Route("/participation")
 */
abstract class AbstractParticipationController extends Controller
{
    protected $participationClass = AbstractParticipation::class;

    /**
     * @Route("/participation/search", name="participation.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function participationSearchAction(Request $request)
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_participation.search');
        $search->handleRequest($request);

        // security check
        if ( ! $this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_trainer.rights.trainer.all.view')) {
            $search->addTermFilter('organization.id', $this->getUser()->getOrganization()->getId());
        }

        return $search->search();
    }

    /**
     * @Route("/{session}/add", name="participation.add", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session\AbstractSession", options={"id" = "session"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function addParticipationAction(AbstractSession $session, Request $request)
    {
        /** @var AbstractParticipation $participation */
        $participation = new $this->participationClass;
        $participation->setSession($session);
        $form = $this->createForm($participation::getFormType(), $participation);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $existingParticipation = null;
                /** @var AbstractParticipation $existingParticipation */
                foreach ($session->getParticipations() as $existingParticipation) {
                    if ($existingParticipation->getTrainer() === $participation->getTrainer()) {
                        $form->get('trainer')->addError(new FormError('Ce formateur est déjà associé à cet évènement.'));
                        break;
                    }
                }

                if (!$existingParticipation || ($existingParticipation->getTrainer() !== $participation->getTrainer())) {
                    $session->addParticipation($participation);
                    $session->updateTimestamps();
                    $session->getTraining()->updateTimestamps();
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($participation);
                    $em->flush();
                }
            }
        }

        return array('form' => $form->createView(), 'participation' => $participation);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="participation.edit", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("participation", class="SygeforTrainingBundle:Session\AbstractParticipation", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "participation", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function editParticipationAction(Request $request, AbstractParticipation $participation)
    {
        // participation can't be created if user has no rights for it
        if ( ! $this->get('security.context')->isGranted('EDIT', $participation->getSession())) {
            throw new AccessDeniedException('Action non autorisée');
        }

        $form = $this->createForm($participation::getFormType(), $participation);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $participation->getSession()->updateTimestamps();
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return array('form' => $form->createView(), 'participation' => $participation);
    }

    /**
     * @Route("/{session}/remove/{participation}", name="participation.remove", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session\AbstractSession", options={"id" = "session"})
     * @ParamConverter("participation", class="SygeforTrainingBundle:Session\AbstractParticipation", options={"id" = "participation"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function removeParticipationAction(AbstractSession $session, AbstractParticipation $participation)
    {
        $session->removeParticipation($participation);
        $session->updateTimestamps();
        $session->getTraining()->updateTimestamps();
        $this->getDoctrine()->getManager()->remove($participation);
        $this->getDoctrine()->getManager()->flush();
        $this->get('fos_elastica.index')->refresh();

        return;
    }
}
