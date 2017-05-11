<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 10:43 AM
 */

namespace Sygefor\Bundle\FrontBundle\Controller;

use Sygefor\Bundle\ApiBundle\Controller\Account\AbstractAnonymousAccountController;
use Sygefor\Bundle\MyCompanyBundle\Entity\Trainee;
use Sygefor\Bundle\FrontBundle\Form\ProfileType;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * This controller regroup all public actions relative to account.
 *
 * @Route("/account")
 */
class AnonymousAccountController extends AbstractAnonymousAccountController
{
    protected $traineeClass = Trainee::class;

    /**
     * Register a new account with data.
     *
     * @Route("/register", name="front.account.register")
     * @Template("@SygeforFront/Account/profile/account-registration.html.twig")
     */
    public function registerAction(Request $request)
    {
        $trainee = new Trainee();
        $shibbolethAttributes = $this->get('security.token_storage')->getToken()->getAttributes();
        if (isset($shibbolethAttributes['title'])) {
            $trainee->setTitle($this->getDoctrine()->getRepository('SygeforCoreBundle:PersonTrait\Term\Title')->findOneBy(
                array('name' => $shibbolethAttributes['title'])
            ));
        }
        $trainee->setOrganization($this->getDoctrine()->getRepository('SygeforCoreBundle:Organization')->find(1));
        if (isset($shibbolethAttributes['sn'])) {
            $trainee->setLastName($shibbolethAttributes['sn']);
        }
        if (isset($shibbolethAttributes['givenName'])) {
            $trainee->setFirstName($shibbolethAttributes['givenName']);
        }
        if (isset($shibbolethAttributes['mail'])) {
            $trainee->setEmail($shibbolethAttributes['mail']);
        }
        if (isset($shibbolethAttributes['telephoneNumber'])) {
            $trainee->setPhoneNumber($shibbolethAttributes['telephoneNumber']);
        }

        // get existing trainee if exists
        $user = $this->getDoctrine()->getRepository('SygeforMyCompanyBundle:Trainee')->findOneBy(array('email' => $this->get('security.context')->getToken()->getAttribute('mail')));
        if ($user && !$user->getIsActive()) {
            $trainee = $user;
        }
        else if ($user) {
            throw new ForbiddenOverwriteException("The user " . $user->getEmail() . " has already an account");
        }

        $form = $this->createForm(new ProfileType($this->get('sygefor_core.access_right_registry')), $trainee);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                parent::registerShibbolethTrainee($request, $trainee, true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($trainee);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Votre profil a bien été créé.');
                $this->get('security.token_storage')->getToken()->setUser($trainee);

                return $this->redirectToRoute('front.account');
            }
        }

        return array('user' => $this->getUser(), 'form' => $form->createView());
    }
}