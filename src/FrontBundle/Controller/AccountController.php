<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 11:00 AM.
 */

namespace FrontBundle\Controller;

use AppBundle\Entity\Trainee\Trainee;
use FrontBundle\Form\Type\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sygefor\Bundle\ApiBundle\Form\Type\RgpdType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use KULeuven\ShibbolethBundle\Security\ShibbolethUserToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
            } else {
                return $this->redirectToRoute('front.account.logout', array('return' => $this->generateUrl('front.program.index', array('shibboleth' => 1, 'error' => 'activation'))));
            }
        } else {
            // redirect user to registration form
            $url = $this->generateUrl('front.account.register');
        }

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     *
     * @Route("/profile", name="front.account.profile")
     * @Template("@Front/Account/profile/profile.html.twig")
     *
     * @return array
     */
    public function profileAction(Request $request)
    {
        /** @var Trainee $agent */
        $form = $this->createForm(new ProfileType(
            $this->get('sygefor_core.access_right_registry')),
            $this->getUser(),
            array(
                'enable_security_check' => false,
            )
        );
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Votre profil a été mis à jour.');

                return $this->redirectToRoute('front.account.profile');
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

	/**
	 * @var Request $request
	 *
	 * @Route("/rgpd", name="api.account.rgpd")
	 * @Template("@Front/Account/profile/rgpd.html.twig")
	 *
	 * @return mixed
	 */
	public function rgpdAction(Request $request)
	{
		/** @var Trainee $trainee */
		$trainee = $this->getUser();
		$form = $this->createForm(new RgpdType(), $trainee);
		if ($request->getMethod() == 'POST') {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$this->getDoctrine()->getManager()->flush();
				$this->get('session')->getFlashBag()->add('success', 'Votre compte a été mis à jour.');

				return $this->redirectToRoute('front.account');
			}
		}

		return array(
			'form' => $form->createView(),
		);
	}

    /**
     * @param Request $request
     * @param string  $return
     *
     * @Route("/logout/{return}", name="front.account.logout", requirements={"return" = ".+"})
     *
     * @return Response
     */
    public function logoutAction(Request $request, $return = null)
    {
        $isShibboleth = $this->get('security.context')->getToken() instanceof ShibbolethUserToken;
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        if ($isShibboleth) {
            return $this->redirect($this->get('shibboleth')->getLogoutUrl($request, $return ? $return : $this->generateUrl('front.program.index')));
        }

        return $this->redirect($this->generateUrl('front.program.index'));
    }
}
