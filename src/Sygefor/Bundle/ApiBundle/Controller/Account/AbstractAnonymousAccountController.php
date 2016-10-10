<?php

namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\View\View;
use Monolog\Logger;
use Sygefor\Bundle\ApiBundle\Form\Type\RegistrationType;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * This controller regroup all public actions relative to account.
 *
 * @Route("/api/account")
 */
abstract class AbstractAnonymousAccountController extends Controller
{
    protected $traineeClass = AbstractTrainee::class;

    /**
     * Register a new account with data.
     *
     * @Route("/register", name="api.account.register", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"})
     * @Method("POST")
     */
    public function registerAction(Request $request)
    {
        /** @var Logger $logger */
        $logger = $this->get('monolog.logger.api');

        try {
            /** @var AbstractTrainee $trainee */
            $trainee = new $this->traineeClass;
            $form    = $this->createForm(RegistrationType::class, $trainee);
            // remove extra fields
            //$data = RegistrationType::extractRequestData($request, $form);
            $data = $request->request->all();

            $logger->info('API : INSCRIPTION');
            $logger->info('data', $data);

            // submit
            $form->submit($data, true);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $token      = $this->get('security.context')->getToken();
                $shibboleth = ($request->get('shibboleth') && $token->hasAttribute('mail') && $token->getAttribute('mail'));
                $this->registerShibbolethTrainee($request, $trainee, $shibboleth);
                $em->persist($trainee);
                $em->flush();

                $clientId = $request->get('client_id');
                if ($shibboleth && $clientId) {
                    // if shibboleth, create a oauth token and return it
                    $generator = $this->get('sygefor_api.oauth.token_generator');

                    return $generator->generateTokenResponse($trainee, $clientId);
                }

                return array('registered' => true);
            }
            else {
                /* @var FormError $error */
                $parser = $this->get('sygefor_api.form_errors.parser');
                // log errors
                $logger->error($form->getErrorsAsString());

                return new View(array('errors' => $parser->parseErrors($form)), 422);
            }
        }
        catch (\Exception $e) {
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
     * @ParamConverter("trainee", class="SygeforTraineeBundle:AbstractTrainee", options={"id" = "id"})
     * @Rest\View()
     */
    public function activateAction(AbstractTrainee $trainee, $token, Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $hash = hash('sha256', $trainee->getId());
        if($token !== $hash) {
            throw new BadRequestHttpException('Invalid token');
        }
        $trainee->setIsActive(true);
        $em->flush();

        // redirect
        $front_url = $this->container->getParameter('front_url');
        $url       = $front_url . '/login?activated=1';
        if($request->getQueryString()) {
            $url .= '&' . $request->getQueryString();
        }

        return new RedirectResponse($url);
    }

    /**
     * Return true if there is an account with the specified email.
     *
     * @Route("/email_check", name="api.account.email_check", defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function emailCheckAction(Request $request)
    {
        $em    = $this->getDoctrine()->getManager();
        $email = $request->get('email');
        if( ! $email) {
            throw new BadRequestHttpException('You must provide an email.');
        }
        $trainee = $em->getRepository('SygeforTraineeBundle:Trainee')->findByEmail($email);

        return array('exists' => $trainee ? true : false);
    }

    /**
     * Reset a password.
     *
     * @Route("/reset_password", name="api.account.reset_password", defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function resetPasswordAction(Request $request)
    {
        $em    = $this->getDoctrine()->getManager();
        $email = $request->get('email');
        if ( ! $email) {
            throw new BadRequestHttpException('You must provide an email.');
        }

        /** @var AbstractTrainee $trainee */
        $trainee = $em->getRepository('SygeforTraineeBundle:AbstractTrainee')->findOneByEmail($email);
        if ( ! $trainee) {
            throw new NotFoundHttpException('Unknown account : ' . $email);
        }

        if ($token = $request->get('token')) {
            list($timestamp, $hash) = explode('.', $token);
            $password               = $request->get('password');

            // check request validity
            if ( ! $hash || ! $timestamp || ! $password) {
                throw new BadRequestHttpException('Invalid request.');
            }

            // check timestamp validity (24h)
            if ((time() - $timestamp) > 24 * 60 * 60) {
                throw new BadRequestHttpException('Invalid request.');
            }

            // check hash validity
            if ($hash !== $this->getTimestampedHash($trainee, $timestamp)) {
                throw new BadRequestHttpException('Invalid request.');
            }

            $trainee->setPlainPassword($password);
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($trainee);
            $trainee->setPassword($encoder->encodePassword($trainee->getPlainPassword(), $trainee->getSalt()));
            $em->flush();

            return array('updated' => true);

        }
        else {
            $timestamp = time();
            $token     = $timestamp . '.' . $this->getTimestampedHash($trainee, $timestamp);

            // Send a email with a generated link with token
            $resetUrl = $this->container->getParameter('front_url') . "/reset-password/$email/$token";

            // send the mail
            $message = \Swift_Message::newInstance()
                ->setFrom($this->container->getParameter('mailer_from'), $this->container->getParameter('mailer_from_name'))
                ->setReplyTo($trainee->getOrganization()->getEmail())
                ->setSubject('SYGEFOR : Réinitialisation de votre mot de passe')
                ->setTo($trainee->getEmail())
                ->setBody($this->renderView('SygeforApiBundle:Account:reset-password.txt.twig', array('trainee' => $trainee, 'resetUrl' => $resetUrl)));
            $sent = $this->get('swiftmailer.mailer.local')->send($message);

            return array('sent' => (bool) $sent);

        }
    }

    /**
     * @param Request $request
     * @param $trainee
     * @param boolean
     */
    protected function registerShibbolethTrainee(Request $request, $trainee, $shibboleth)
    {
        $trainee->setIsActive(false);

        // shibboleth
        $token      = $this->get('security.context')->getToken();

        if ($shibboleth) {
            // if shibboleth, save persistent_id and force mail
            // and set active to true
            $persistentId = $token->getAttribute('persistent_id');
            $email        = $token->getAttribute('mail');
            $trainee->setShibbolethPersistentId($persistentId ? $persistentId : $email);
            $trainee->setEmail($email);
            $trainee->setIsActive(true);
        }
        else {
            $trainee->setSendActivationMail(array(
                'redirect' => $request->get('redirect'),
            ));
        }

        // if a password has been
        if ($trainee->getPlainPassword()) {
            $password = $trainee->getPlainPassword();
        } else {
            $password = TraineeRepository::generatePassword();
        }
        $trainee->setPlainPassword($password);

        // set isPaying from publicType
        //$trainee->setIsPaying($trainee->getPublicType()->getIsPaying());

        //$trainee->setSendCredentialsMail(true);
    }

    /**
     * @param AbstractTrainee $trainee
     * @param string  $timestamp
     *
     * @return string
     */
    private function getTimestampedHash(AbstractTrainee $trainee, $timestamp)
    {
        return hash('sha256', $timestamp . '.' . $trainee->getPassword());
    }
}
