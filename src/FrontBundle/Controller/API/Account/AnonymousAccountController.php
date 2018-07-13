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
use AppBundle\Entity\Trainee\Trainee;
use FrontBundle\Form\Type\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sygefor\Bundle\CoreBundle\Entity\AbstractOrganization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
            $this->updateFromShibboleth($trainee);

            // get existing trainee if exists
            $user = $this->getDoctrine()->getRepository($this->traineeClass)->findOneBy(array(
                'email' => $this->get('security.context')->getToken()->getAttribute('mail'),
            ));
            if ($user && $user->getIsActive()) {
                $trainee = $user;
            } elseif ($user) {
                throw new ForbiddenOverwriteException('The user '.$user->getEmail().' has already an account');
            }

            $trainee->setShibbolethPersistentId($this->get('security.context')->getToken()->getAttribute('persistent_id'));
            $form = $this->createForm(new ProfileType(
                $this->get('sygefor_core.access_right_registry')),
                $trainee
            );
            if ($request->getMethod() === 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    parent::registerShibbolethTrainee($request, $trainee, true);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trainee);
                    $em->flush();

                    if (!$trainee->getIsActive()) {
                        $this->notifyUnactiveTrainee($trainee);
                    }

                    $this->get('session')->getFlashBag()->add('success', 'Votre profil a bien été créé.');
                    $this->get('security.token_storage')->getToken()->setUser($trainee);

                    return $this->redirectToRoute('front.account');
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
     * @var Trainee $trainee
     * @var string $token
     * @var Request $request
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
        $trainee->setIsActive(true);
        $trainee->setSendCredentialsMail(true);
        $em->flush();

        return $this->redirectToRoute('front.page.login', array('activated' => 1));
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

    /**
     * @param Trainee $trainee
     *
     * @return int
     *
     * @throws \Twig_Error
     */
    protected function notifyUnactiveTrainee(Trainee $trainee)
    {
        $message = \Swift_Message::newInstance(null, null, 'text/html', null);
        $message->setFrom($this->getParameter('mailer_from'), $trainee->getOrganization()->getName());
        $message->setReplyTo($trainee->getEmail());
        $message->setTo($trainee->getOrganization()->getEmail());
        $message->setSubject('Le compte de '.$trainee->getFullName().' doit être vérifié');
        $message->setBody($this->get('templating')->render('trainee/new_trainee_notification.html.twig', array(
            'trainee' => $trainee,
            'host' => $this->getParameter('back_host'),
        )));
        $message->addPart(Html2Text::convert($message->getBody()), 'text/plain');

        return $this->get('mailer')->send($message);
    }
}
