<?php
namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityNotFoundException;
use Elastica\Filter\Query;
use Elastica\Query\FuzzyLikeThis;
use Elastica\Query\Match;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\QueryString;
use Elastica\Suggest\Phrase;
use FOS\RestBundle\View\View;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Monolog\Logger;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sygefor\Bundle\ApiBundle\Controller\SecurityController;
use Sygefor\Bundle\ApiBundle\Form\Type\RegistrationType;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeArray;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Sygefor\Bundle\TraineeBundle\Form\ArrayTraineeType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeArrayType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * This controller regroup all public actions relative to account
 *
 * @package Sygefor\Bundle\TraineeBundle\Controller
 * @Route("/api/account")
 */
class AnonymousAccountController extends Controller
{
    /**
     * Register a new account with data
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
            $trainee = new Trainee();
            $form = $this->createForm(new RegistrationType(), $trainee);
            // remove extra fields
            $data = RegistrationType::extractRequestData($request, $form);

            $logger->info("API : INSCRIPTION");
            $logger->info("data", $data);

            // submit
            $form->submit($data, true);
            if($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $trainee->setIsActive(false);

                // shibboleth
                $token = $this->get('security.context')->getToken();
                $shibboleth = ($request->get('shibboleth') && $token->hasAttribute('mail') && $token->getAttribute('mail'));

                if($shibboleth) {
                    // if shibboleth, save persistent_id and force mail
                    // and set active to true
                    $persistentId = $token->getAttribute('persistent_id');
                    $email = $token->getAttribute('mail');
                    $trainee->setShibbolethPersistentId($persistentId ? $persistentId : 1);
                    $trainee->setEmail($email);
                    $trainee->setIsActive(true);
                } else {
                    $trainee->setSendActivationMail(array(
                        'redirect' => $request->get('redirect')
                      ));
                }

                // if a password has been
                if($trainee->getPlainPassword()) {
                    $password = $trainee->getPlainPassword();
                } else {
                    $password = TraineeRepository::generatePassword();
                }
                $trainee->setPlainPassword($password);
                //$trainee->setSendCredentialsMail(true);
                $em->persist($trainee);
                $em->flush();

                $clientId = $request->get('client_id');
                if($shibboleth && $clientId) {
                    // if shibboleth, create a oauth token and return it
                    $generator = $this->get('sygefor_api.oauth.token_generator');
                    return $generator->generateTokenResponse($trainee, $clientId);
                }

                return array("registered" => true);
            } else {
                /** @var FormError $error */
                $parser = $this->get('sygefor_api.form_errors.parser');
                // log errors
                $logger->error($form->getErrorsAsString());
                return new View(array('errors' => $parser->parseErrors($form)), 422);
            }

        } catch(\Exception $e) {
            // log exception
            $logger->critical(get_class($e));
            $logger->critical($e->getMessage());
            throw $e;
        }
    }

    /**
     * Activate an account
     *
     * @Route("/activate/{id}/{token}", name="api.account.activate", defaults={"_format" = "json"})
     * @ParamConverter("trainee", class="SygeforTraineeBundle:Trainee", options={"id" = "id"})
     * @Rest\View()
     */
    public function activateAction(Trainee $trainee, $token, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $hash = hash('sha256', $trainee->getId());
        if($token != $hash) {
            throw new BadRequestHttpException("Invalid token");
        }
        $trainee->setIsActive(true);
        $em->flush();

        // redirect
        $front_url =  $this->container->getParameter('front_url');
        $url = $front_url . '/login?activated=1';
        if($request->getQueryString()) {
            $url .= '&'.$request->getQueryString();
        }
        return new RedirectResponse($url);
    }

    /**
     * Return true if there is an account with the specified email
     *
     * @Route("/email_check", name="api.account.email_check", defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function emailCheckAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $email = $request->get('email');
        if(!$email) {
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
        $em = $this->getDoctrine()->getManager();
        $email = $request->get('email');
        if(!$email) {
            throw new BadRequestHttpException('You must provide an email.');
        }

        /** @var Trainee $trainee */
        $trainee = $em->getRepository('SygeforTraineeBundle:Trainee')->findOneByEmail($email);
        if(!$trainee) {
            throw new NotFoundHttpException('Unknown account : ' . $email);
        }

        if($token = $request->get('token')) {
            list($timestamp, $hash) = explode(".", $token);
            $password = $request->get('password');

            // check request validity
            if(!$hash || !$timestamp || !$password) {
                throw new BadRequestHttpException('Invalid request.');
            }

            // check timestamp validity (24h)
            if((time() - $timestamp) > 24 * 60 * 60) {
                throw new BadRequestHttpException('Invalid request.');
            }

            // check hash validity
            if($hash != $this->getTimestampedHash($trainee, $timestamp)) {
                throw new BadRequestHttpException('Invalid request.');
            }

            $trainee->setPlainPassword($password);
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($trainee);
            $trainee->setPassword($encoder->encodePassword($trainee->getPlainPassword(), $trainee->getSalt()));
            $em->flush();
            return array('updated' => true);

        } else {

            $timestamp = time();
            $token = $timestamp.'.'.$this->getTimestampedHash($trainee, $timestamp);

            // Send a email with a generated link with token
            $resetUrl = $this->container->getParameter('front_url') . "/reset-password/$email/$token";

            // send the mail
            $message = \Swift_Message::newInstance()
              ->setFrom($this->container->getParameter('mailer_from'), $this->container->getParameter('mailer_from_name'))
              ->setReplyTo($trainee->getOrganization()->getEmail())
              ->setSubject('SYGEFOR : Réinitialisation de votre mot de passe')
              ->setTo($trainee->getEmail())
              ->setBody($this->renderView('SygeforApiBundle:Account:reset-password.txt.twig', array('trainee' => $trainee, 'resetUrl' => $resetUrl)))
            ;
            $sent = $this->get('swiftmailer.mailer.local')->send($message);
            return array('sent' => !!$sent);

        }
    }

    /**
     * @param Trainee $trainee
     * @param string $timestamp
     * @return string
     */
    private function getTimestampedHash(Trainee $trainee, $timestamp) {
        return hash('sha256', $timestamp.'.'.$trainee->getPassword());
    }
}
