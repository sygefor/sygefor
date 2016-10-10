<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 10:42 AM
 */

namespace Sygefor\Bundle\FrontBundle\Controller;


use Sygefor\Bundle\ApiBundle\Controller\Account\AbstractRegistrationAccountController;
use Sygefor\Bundle\MyCompanybundle\Entity\Inscription;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     * @Template("@SygeforFront/Account/registration/checkout.html.twig")
     */
    public function checkoutAction(Request $request, $sessions = array())
    {
        if (!$this->getUser()->getIsActive()) {
            throw new ForbiddenOverwriteException("You account is not active");
        }

        $inscription = $this->getDoctrine()->getManager()->getRepository('SygeforMyCompanyBundle:Inscription')->find($request->get('inscriptionId'));
        $this->sendCheckoutNotification(array($inscription), $inscription->getTrainee());

        return $this->redirectToRoute('front.account.registrations');
    }

    /**
     * Registrations.
     *
     * @Route("/registrations", name="front.account.registrations")
     * @Template("@SygeforFront/Account/registration/registrations.html.twig")
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
            if ($inscription->getSession()->getDateBegin() < $now) {
                $past[] = $inscription;
                $inscription->upcoming = false;
            }
            else {
                $inscription->upcoming = true;
                $upcoming[] = $inscription;
                $upcomingIds[] = $inscription->getId();
            }
        }

        return array('user' => $this->getUser(), 'upcoming' => $upcoming, 'past' => $past, 'upcomingIds' => implode(',', $upcomingIds));
    }

    /**
     * Desist a registration.
     *
     * @Route("/registration/{id}/desist", name="front.account.registration.desist")
     * @Template("@SygeforFront/Account/registration/registration-desist.html.twig")
     */
    public function desistAction($id, Request $request)
    {
        $registration = $this->getDoctrine()->getRepository('SygeforInscriptionBundle:AbstractInscription')->find($id);
        $registration->pending = $registration->getInscriptionStatus()->getId() === 1;
        if ($request->getMethod() === "POST") {
            if (parent::desistAction($id, $request)['desisted']) {
                $this->get('session')->getFlashBag()->add('success', 'Votre désistement a bien été enregistré.');
                return $this->redirectToRoute('front.account.registrations');
            }
        }

        return array('user' => $this->getUser(), 'registration' => $registration);
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
}