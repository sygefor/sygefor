<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 11:00 AM
 */

namespace Sygefor\Bundle\FrontBundle\Controller;


use Sygefor\Bundle\FrontBundle\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="front.account")
     *
     * @return RedirectResponse
     */
    public function accountAction(Request $request)
    {
        $user = $this->getUser();
        if ($user) {
            if ($user->getIsActive()) {
                // redirect user to registrations pages
                $url = $this->generateUrl('front.account.registrations');
            }
            else {
                return $this->redirectToRoute('front.account.logout', array('return' => $this->generateUrl('front.public.index', array('shibboleth' => 1, 'error' => 'activation'))));
            }
        }
        else {
            // redirect user to registration form
            $url = $this->generateUrl('front.account.register');
        }

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     *
     * @Route("/profile", name="front.account.profile")
     * @Template("@SygeforFront/Account/profile/profile.html.twig")
     *
     * @return array
     */
    public function profileAction(Request $request)
    {
        $form = $this->createForm(new ProfileType($this->get('sygefor_core.access_right_registry')), $this->getUser());
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Votre profil a été mis à jour.');
            }
        }

        return array('user' => $this->getUser(), 'form' => $form->createView());
    }

    /**
     * @param Request $request
     * @param string $return
     *
     * @Route("/logout/{return}", name="front.account.logout", requirements={"return" = ".+"})
     *
     * @return array
     */
    public function logoutAction(Request $request, $return = null)
    {
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        return $this->redirect($this->get('shibboleth')->getLogoutUrl($request, $return ? $return : $this->generateUrl('front.public.index')));
    }
}