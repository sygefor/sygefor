<?php

namespace Sygefor\Bundle\InscriptionBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\InscriptionBundle\Entity\AbstractInscription;
use Sygefor\Bundle\InscriptionBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\InscriptionBundle\Form\BaseInscriptionType;
use Sygefor\Bundle\TrainingBundle\Entity\Session\AbstractSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InscriptionController.
 *
 * @Route("/inscription")
 */
abstract class AbstractInscriptionController extends Controller
{
    protected $inscriptionClass = AbstractInscription::class;

    /**
     * @Route("/search", name="inscription.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_inscription.search');
        $search->handleRequest($request);

        // security check : training
        if(!$this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_inscription.rights.inscription.all.view')) {
            $search->addTermFilter('session.training.organization.id', $this->getUser()->getOrganization()->getId());
        }

        return $search->search();
    }

    /**
     * @Route("/create/{session}", name="inscription.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session\AbstractSession", options={"id" = "session"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(AbstractSession $session, Request $request)
    {
        /** @var AbstractInscription $inscription */
        $inscription = $this->createInscription($session);
        /** @var BaseInscriptionType $inscriptionClass */
        $inscriptionClass = $inscription::getFormType();

        $form        = $this->createForm(new $inscriptionClass($session->getTraining()->getOrganization()), $inscription);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($inscription);
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'inscription' => $inscription);
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="inscription.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="inscription", permissions="VIEW")
     * @ParamConverter("inscription", class="SygeforInscriptionBundle:AbstractInscription", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(AbstractInscription $inscription, Request $request)
    {
        if (!$this->get('security.context')->isGranted('EDIT', $inscription)) {
            return array('inscription' => $inscription);
        }
        /** @var BaseInscriptionType $inscriptionClass */
        $inscriptionClass = $inscription::getFormType();

        $form = $this->createForm(new $inscriptionClass($inscription->getSession()->getTraining()->getOrganization()), $inscription);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'inscription' => $inscription);
    }

    /**
     * @Route("/{id}/remove", name="inscription.delete", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="inscription", permissions="DELETE")
     * @ParamConverter("inscription", class="SygeforInscriptionBundle:AbstractInscription", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(AbstractInscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($inscription);
        $em->flush();
        $this->get('fos_elastica.index')->refresh();

        return array();
    }

    /**
     * @param AbstractSession $session
     *
     * @return AbstractInscription
     */
    protected function createInscription($session)
    {
        $em          = $this->getDoctrine()->getManager();
        $inscription = new $this->inscriptionClass;
        $inscription->setSession($session);

        // national inscription status
        $defaultInscriptionStatus = $em->getRepository('SygeforInscriptionBundle:Term\InscriptionStatus')->findOneBy(
            array('organization' => null, 'status' => InscriptionStatus::STATUS_PENDING));

        // local inscription status if national is not found
        if (!$defaultInscriptionStatus) {
            $defaultInscriptionStatus = $em->getRepository('SygeforInscriptionBundle:Term\InscriptionStatus')->findOneBy(
                array('organization' => $this->getUser()->getOrganization(), 'status' => InscriptionStatus::STATUS_PENDING));
        }

        $inscription->setInscriptionStatus($defaultInscriptionStatus);

        return $inscription;
    }
}
