<?php

namespace AppBundle\Controller;

use Html2Text\Html2Text;
use AppBundle\Entity\Trainee\Trainee;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Constraints\Length;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Symfony\Component\Validator\Constraints\NotBlank;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\CoreBundle\Controller\AbstractTraineeController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class TraineeController.
 *
 * @Route("/trainee")
 */
class TraineeController extends AbstractTraineeController
{
    /**
     * @var string
     */
    protected $traineeClass = Trainee::class;

    /**
     * @Route("/create", name="trainee.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @Security("is_granted('CREATE', 'SygeforCoreBundle:AbstractTrainee')")
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Request $request)
    {
        /** @var Trainee $trainee */
        $trainee = new $this->traineeClass();
        $trainee->setOrganization($this->getUser()->getOrganization());

        //trainee can't be created if user has no rights for it
        if (!$this->get('security.context')->isGranted('CREATE', $trainee)) {
            throw new AccessDeniedException('Action non autorisée');
        }

        $form = $this->createForm($trainee::getFormType(), $trainee);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                //setting a default password and encoding it.
                $password = uniqid();
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($trainee);
                $trainee->setPassword($encoder->encodePassword($password, $trainee->getSalt()));

                $trainee->setIsActive(false);
                $trainee->setSendActivationMail(true);
                $trainee->setSendCredentialsMail(false);
                $em = $this->getDoctrine()->getManager();
                $em->persist($trainee);
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'trainee' => $trainee);
    }

    /**
     * @Route("/{id}/toggleActivation", requirements={"id" = "\d+"}, name="trainee.toggleActivation", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("trainee", class="AppBundle:Trainee\Trainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     * @Method("POST")
     */
    public function toggleActivationAction(Request $request, Trainee $trainee)
    {
        //access right is checked inside controller, so to be able to send specific error message
        if (!$this->get('security.context')->isGranted('EDIT', $trainee)) {
            throw new AccessDeniedException("Vous n'avez pas accès aux informations détaillées de cet utilisateur");
        }

        $trainee->setIsActive(!$trainee->getIsActive());
        $this->getDoctrine()->getManager()->flush();

        if ($trainee->getIsActive()) {
            $body = $this->get('templating')->render('trainee/admin_activation.html.twig', array('trainee' => $trainee));

            // send the mail
            $message = \Swift_Message::newInstance(null, null, 'text/html', null)
                ->setFrom($this->container->getParameter('mailer_from'), $this->container->getParameter('mailer_from_name'))
                ->setReplyTo($trainee->getOrganization()->getEmail())
                ->setSubject('SYGEFOR : activiation de votre compte')
                ->setTo($trainee->getEmail())
                ->setBody($body);
            $message->addPart(Html2Text::convert($message->getBody()), 'text/plain');
            $this->get('mailer')->send($message);
        }

        return array('trainee' => $trainee);
    }

    /**
     * @Route("/{id}/changepwd", name="trainee.changepwd", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="trainee", permissions="EDIT")
     * @ParamConverter("trainee", class="AppBundle:Trainee\Trainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function changePasswordAction(Request $request, Trainee $trainee)
    {
        $form = $this->createFormBuilder($trainee)
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'constraints' => array(new Length(array('min' => 6)), new NotBlank()),
                'required' => true,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options' => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmation'),
            ))
            ->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // password encoding is handle by TraineeListener
                $trainee->setPassword(null);
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return array('form' => $form->createView(), 'trainee' => $trainee);
    }
}
