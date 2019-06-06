<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 10:43 AM.
 */

namespace FrontBundle\Controller\API\Account;

use Monolog\Logger;
use Html2Text\Html2Text;
use Html2Text\Html2TextException;
use Symfony\Component\Form\FormError;
use AppBundle\Entity\Trainee\Trainee;
use FrontBundle\Form\Type\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Sygefor\Bundle\CoreBundle\Entity\AbstractOrganization;
use Sygefor\Bundle\CoreBundle\Form\Type\UpdatePasswordType;
use KULeuven\ShibbolethBundle\Security\ShibbolethUserToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Sygefor\Bundle\ApiBundle\Controller\Account\AbstractAnonymousAccountController;

/**
 * This controller regroup all public actions relative to account.
 *
 * @Route("/account")
 */
class AnonymousAccountController extends AbstractAnonymousAccountController
{
    protected $traineeClass = Trainee::class;

    /**
     * @Route("/shibboleth/login", name="front.shibboleth.login")
     */
    public function shibbolethLoginAction(Request $request)
    {
        $url = $this->generateUrl('front.account', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->redirect($this->get('shibboleth')->getLoginUrl($request, $url));
    }

    /**
     * Register a new account with data.
     *
     * @Route("/register", name="front.account.register")
     * @Template("@Front/Account/profile/account-registration.html.twig")
     */
    public function registerAction(Request $request)
    {
        /** @var Logger $logger */
        $logger = $this->get('monolog.logger.api');

        try {
            /** @var Trainee $trainee */
            $trainee = new $this->traineeClass();

            // get existing trainee if exists
            $email = $request->get('email');
            $isShibboleth = $this->get('security.token_storage')->getToken() instanceof ShibbolethUserToken;
            if ($isShibboleth) {
                $this->updateFromShibboleth($trainee);
                $email = $this->get('security.context')->getToken()->getAttribute('mail');
                $trainee->setShibbolethPersistentId($this->get('security.context')->getToken()->getAttribute('persistent_id'));
            }

            $user = $this->getDoctrine()->getRepository($this->traineeClass)->findOneByEmail($email);
            if ($user && $user->getIsActive()) {
                $trainee = $user;
            } elseif ($user) {
                throw new ForbiddenOverwriteException('The user '.$user->getEmail().' has already an account');
            }

            $form = $this->createForm(new ProfileType(
                $this->get('sygefor_core.access_right_registry')),
                $trainee
            );
            if ($request->getMethod() === 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    parent::registerShibbolethTrainee($request, $trainee, $isShibboleth);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trainee);
                    $em->flush();

                    if ($isShibboleth) {
                        $this->get('session')->getFlashBag()->add('success', 'Votre profil a bien été créé.');
                        $this->get('security.token_storage')->getToken()->setUser($trainee);

                        return $this->redirectToRoute('front.account');
                    } else {
                        $factory = $this->get('security.encoder_factory');
                        $encoder = $factory->getEncoder($trainee);
                        $trainee->setPassword($encoder->encodePassword($trainee->getPlainPassword(), $trainee->getSalt()));
                        $this->get('session')->getFlashBag()->add('success',
                            'Votre profil a bien été créé. '.
                            'Veuillez consulter vos courriels pour terminer la procédure.'
                        );

                        return $this->redirectToRoute('front.program.index');
                    }
                }
            }

            return array(
                'form' => $form->createView(),
            );
        } catch (\Exception $e) {
            // log exception
            $logger->critical(get_class($e));
            $logger->critical($e->getMessage());

            throw $e;
        }
    }

    /**
     * Activate an account.
     *
     * @Route("/activate/{id}/{token}", name="api.account.activate", defaults={"_format" = "json"})
     * @Rest\View()
     *
     * @var AbstractTrainee $trainee
     * @var string          $token
     * @var Request         $request
     *
     * @return RedirectResponse
     */
    public function activateAction(AbstractTrainee $trainee, $token, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $hash = hash('sha256', $trainee->getId());
        if ($token !== $hash) {
            throw new BadRequestHttpException('Invalid token');
        }
	    $trainee->setSendCredentialsMail(!$trainee->getIsActive());
	    $trainee->setIsActive(true);
	    $trainee->updateTimestamps();
	    $em->flush();

        return $this->redirectToRoute('front.page.login', array('activated' => 1));
    }

    /**
     * @Route("/reset_password", name="front.page.reset_password")
     */
    public function resetPasswordAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Votre courriel',
                'attr' => [
                    'placeholder' => 'Votre courriel'
                ]
            ])->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $email = $form->get('email')->getData();
                /** @var Trainee $trainee */
                $trainee = $this->getDoctrine()->getRepository(Trainee::class)->findOneByEmail($email);
                if ($trainee) {
                    $timestamp = time();
                    $token = $timestamp.'.'.$this->getTimestampedHash($trainee, $timestamp);
                    // Send a email with a generated link with token
                    $resetUrl = $this->generateUrl('front.page.choose_password', [
                        'email' => $email,
                        'token' => $token
                    ],UrlGeneratorInterface::ABSOLUTE_URL);

	                $sent = $this->container->get('notification.mailer')->send('trainee.reset_password', $trainee, [
		                'resetUrl' => $resetUrl,
		                'recipient' => $trainee,
	                ]);
                    if ($sent) {
                        $this->get('session')->getFlashBag()->add('success', 'Veuillez consulter vos courriels.');
                    }
                    else {
                        $this->get('session')->getFlashBag()->add('error', 'Une erreur est survenue pendant la procédure.');
                    }
                }
                else {
                    $this->get('session')->getFlashBag()->add('error', 'Cette compte n\'existe pas.');
                    $form->get('email')->addError(new FormError("Veuillez renseigner votre adresse email."));
                }

            }
        }

        return $this->render('@Front/Account/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/choose_password/{email}/{token}", name="front.page.choose_password")
     * @ParamConverter("trainee", class="AppBundle:Trainee\Trainee", options={"email" = "email"})
     */
    public function choosePasswordAction(Request $request, Trainee $trainee, $token)
    {
	    list($timestamp, $hash) = explode('.', $token);
	    $error = null;
	    $timestampHash = $this->getTimestampedHash($trainee, $timestamp);
	    // check request validity
	    if ($hash !== $timestampHash) {
		    $error = "Il semble y avoir un problème avec le lien.";
	    }
	    // check timestamp validity (24h)
	    if ((time() - $timestamp) > 24 * 60 * 60) {
		    $error = "Le lien n'est plus valide.";
	    }
	    $form = $this->createForm(new UpdatePasswordType(), $trainee, ['disabled' => $error !== null]);
	    if ($error) {
		    $form->addError(new FormError($error));
	    }

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // password encoding is handle by PasswordEncoderSubscriber
                $trainee->setPassword(null);
                $this->getDoctrine()->getManager()->flush();

                $this->get('session')->getFlashBag()->add('success', 'Votre mot de passe a été mis à jour.');

                return $this->redirectToRoute('front.page.login');
            }
        }

        return $this->render('@Front/Account/choose_password.html.twig', [
            'form' => $form->createView(),
            'email' => $trainee->getEmail(),
        ]);
    }

    /**
     * Save Shibboleth attributes data.
     *
     * @param Trainee $trainee
     */
    protected function updateFromShibboleth(Trainee $trainee)
    {
        $shibbolethAttributes = $this->get('security.token_storage')->getToken()->getAttributes();
        if (isset($shibbolethAttributes['title'])) {
            $trainee->setTitle($this->getDoctrine()->getRepository(Title::class)->findOneBy(
                array('name' => $shibbolethAttributes['title'])
            ));
        }
        $trainee->setOrganization($this->getDoctrine()->getRepository(AbstractOrganization::class)->find(1));
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
    }
}
