<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 11:00 AM.
 */

namespace FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/page")
 */
class PageController extends Controller
{
    /**
     * @Route("/login", name="front.page.login")
     */
    public function loginAction(Request $request)
    {
        if ($request->get('activated') == 1) {
            $this->get('session')->getFlashBag()->add('success',
                'Votre compte a été vérifié. '.
                'Vous pouvez maintenant vous connecter en utilisant vos identifiants ou votre compte universitaire.');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@Front/Page/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @Route("/faq", name="front.page.faq")
     */
    public function faqAction()
    {
        return $this->render('@Front/Page/faq.html.twig');
    }

    /**
     * @Route("/about", name="front.page.about")
     */
    public function aboutAction()
    {
        return $this->render('@Front/Page/about.html.twig');
    }

    /**
     * @Route("/legalNotice", name="front.page.legalNotice")
     */
    public function legalNoticeAction()
    {
        return $this->render('@Front/Page/legalNotice.html.twig');
    }
}
