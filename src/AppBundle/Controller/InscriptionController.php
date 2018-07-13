<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inscription;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sygefor\Bundle\CoreBundle\Entity\AbstractInscription;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;
use Sygefor\Bundle\CoreBundle\Controller\AbstractInscriptionController;

/**
 * Class InscriptionController.
 *
 * @Route("/inscription")
 */
class InscriptionController extends AbstractInscriptionController
{
    protected $inscriptionClass = Inscription::class;

    /**
     * @Route("/search", name="inscription.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_inscription.search');
        $search->handleRequest($request);

        // security check : training
        if (!$this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_core.access_right.inscription.all.view')) {
            if ($this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_core.access_right.inscription.all.view')) {
                $search->addTermFilter('session.training.organization.id', $this->getUser()->getOrganization()->getId());
            } elseif ($this->get('sygefor_core.access_right_registry')->hasAccessRight('app.access_right.trainees_inscription.own.view')) {
                $search->addTermFilter('trainee.organization.id', $this->getUser()->getOrganization()->getId());
            } else {
                throw new AccessDeniedException();
            }
        }

        return $search->search();
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="inscription.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="inscription", permissions="VIEW")
     * @ParamConverter("inscription", class="SygeforCoreBundle:AbstractInscription", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     * @SatisfiesParentSecurityPolicy
     */
    public function viewAction(AbstractInscription $inscription, Request $request)
    {
        $latestInscriptions = Inscription::getTraineeThemeInscription(
            $this->get('sygefor_inscription.search'),
            $inscription->getTrainee(),
            $inscription->getSession()->getTraining()->getTheme(),
            $inscription
        );

        if (!$this->get('security.context')->isGranted('EDIT', $inscription)) {
            if ($this->get('security.context')->isGranted('VIEW', $inscription)) {
                return array(
                    'inscription' => $inscription,
                    'latestInscriptions' => $latestInscriptions
                );
            }

            throw new AccessDeniedException('Action non autorisée');
        }

        /** @var Inscription $inscriptionClass */
        $inscriptionClass = $inscription::getFormType();
        $form = $this->createForm(new $inscriptionClass($inscription->getSession()->getTraining()->getOrganization()), $inscription);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }
        }

        return array(
            'form' => $form->createView(),
            'inscription' => $inscription,
            'latestInscriptions' => $latestInscriptions,
        );
    }
}
