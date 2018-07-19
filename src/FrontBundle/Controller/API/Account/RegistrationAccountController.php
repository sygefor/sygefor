<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 10:42 AM.
 */

namespace FrontBundle\Controller\API\Account;

use Html2Text\Html2Text;
use AppBundle\Entity\Inscription;
use AppBundle\Entity\Trainee\Trainee;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Sygefor\Bundle\ApiBundle\Controller\Account\AbstractRegistrationAccountController;

/**
 * This controller regroup actions related to registration.
 *
 * @Route("/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class RegistrationAccountController extends AbstractRegistrationAccountController
{
    protected $inscriptionClass = Inscription::class;

    /**
     * Checkout registrations cart.
     *
     * @Route("/checkout", name="front.account.checkout")
     * @Template("@Front/Account/registration/checkout.html.twig")
     */
    public function checkoutAction(Request $request, $sessions = array())
    {
        if (!$this->getUser()->getIsActive()) {
            throw new ForbiddenOverwriteException('You account is not active');
        }
        /** @var Inscription $inscription */
        $inscription = $this->getDoctrine()->getManager()->getRepository($this->inscriptionClass)->find($request->get('inscriptionId'));
        /** @var Trainee $trainee */
        $trainee = $inscription->getTrainee();
        $this->sendCheckoutNotification(array($inscription), $trainee);

        return $this->redirectToRoute('front.account.registrations');
    }

    /**
     * Registrations.
     *
     * @Route("/registrations", name="front.account.registrations")
     * @Template("@Front/Account/registration/registrations.html.twig")
     * @Method("GET")
     */
    public function registrationsAction(Request $request)
    {
        $inscriptions = parent::registrationsAction($request);

        $upcoming = array();
        $upcomingIds = array();
        $past = array();
        $now = new \DateTime();
        foreach ($inscriptions as $inscription) {
            $inscription->pending = $inscription->getInscriptionStatus()->getId() === 1;
            if ($inscription->getSession()->getDateBegin() < $now) {
                $past[] = $inscription;
                $inscription->upcoming = false;
            } else {
                $inscription->upcoming = true;
                $upcoming[] = $inscription;
                $upcomingIds[] = $inscription->getId();
            }
        }

        return array('upcoming' => $upcoming, 'past' => $past, 'upcomingIds' => implode(',', $upcomingIds));
    }

    /**
     * Desist a registration.
     *
     * @Route("/registration/{id}/desist", name="front.account.registration.desist")
     * @Template("@Front/Account/registration/registration-desist.html.twig")
     */
    public function desistAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $registration = $em->getRepository($this->inscriptionClass)->find($id);
        $registration->pending = $registration->getInscriptionStatus()->getId() === 1;

        if (!$registration->pending) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() === 'POST') {
            if (!$registration) {
                throw new NotFoundHttpException('Unknown registration.');
            }

            // check date
            if ($registration->getSession()->getDateBegin() < new \DateTime()) {
                throw new BadRequestHttpException('You cannot desist from a past session.');
            }

            // check status
            if ($registration->getInscriptionStatus()->getStatus() > InscriptionStatus::STATUS_ACCEPTED) {
                throw new BadRequestHttpException('Your registration has already been rejected.');
            }

            // ok, let's go
            if ($registration->getInscriptionStatus()->getStatus() <= InscriptionStatus::STATUS_WAITING) {
                // if the inscription is pending, just delete it
                $em->remove($registration);
            } else {
                // else set the status to "Desist"
                $status = $this->getDesistInscriptionStatus($this->getUser());
                $registration->setInscriptionStatus($status);
            }
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Votre désistement a bien été enregistré.');

            return $this->redirectToRoute('front.account.registrations');
        }

        return array('registration' => $registration);
    }

    /**
     * Download a authorization form.
     *
     * @Route("/registration/{ids}/authorization", name="front.account.registration.authorization")
     * @Method("GET")
     */
    public function authorizationAction($ids, Request $request)
    {
        return parent::authorizationAction($ids, $request);
    }

    /**
     * Generate authorization forms.
     *
     * @param $trainee
     * @param $registrations
     * @param $templates
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getAuthorizationForms($trainee, $registrations, $templates)
    {
        $repository = $this->get('doctrine')->getManager()->getRepository($this->inscriptionClass);
        $registrations = $repository->findBy(array('id' => $registrations));

        /** @var Inscription $registration */
        foreach ($registrations as $key => $registration) {
            if ($registration->getTrainee() !== $trainee) {
                throw new \InvalidArgumentException('The registration does not belong to the trainee : '.$registration->getId());
            }
            if ($registration->getInscriptionStatus()->getMachineName() === 'desist') {
                unset($registration[$key]);
            }
        }

        if (is_string($templates)) {
            $templates = array($templates);
        }

        // build pages
        $forms = array();
        $variables = array(
            'trainee' => $trainee,
            'registrations' => $registrations,
        );

        foreach ($templates as $key => $template) {
            $forms[0][$key] = $this->renderView($template, $variables);
        }

        return $forms;
    }
}
