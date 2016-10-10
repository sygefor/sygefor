<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Sygefor\Bundle\TrainerBundle\Entity\AbstractTrainer;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/api/email")
 */
class EmailController extends AbstractController
{
    /**
     * @Route("/send", name="api.email.send", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api"}, serializerEnableMaxDepthChecks=true)
     */
    public function sendEmailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var AbstractTrainer $trainer */
        $trainer = $em->getRepository('SygeforTrainerBundle:AbstractTrainer')->find($request->request->get('to'));
        if (!$trainer) {
            throw new InvalidArgumentException('The trainer does not exist');
        }
        if ($trainer->isIsAllowSendMail() === false && $trainer->isIsAllowSendMail() !== null) {
            return array('errors' => 'Vous ne pouvez pas envoyer de courriel à ce formateur');
        }

        $training = null;
        if ($request->request->get('training')) {
            $training = $em->getRepository('SygeforTrainingBundle:Training\AbstractTraining')->find($request->request->get('training'));
            if ( ! $training) {
                throw new InvalidArgumentException('The training does not exist');
            }
        }

        if( ! filter_var($request->request->get('from'), FILTER_VALIDATE_EMAIL)){
            return array('errors' => 'Veuillez renseigner une adresse électronique valide');
        }

        $message = \Swift_Message::newInstance();
        $message->setFrom($this->container->getParameter('mailer_from'), 'Sygefor3');
        $message->setReplyTo($request->request->get('from'));
        $message->setTo($trainer->getEmail());
        $message->setSubject("Message d'un stagiaire - " . $request->request->get('subject'));
        $body = $request->request->get('body');

        // add training link and name
        if ($training) {
            $body = 'Stage: ' . $this->getParameter('front_url') . '/#/program/' . $training->getId() . '?from=true / ' . $training->getName() . "\n" . $body;
        }

        // add trainee email
        $body = 'Courriel du stagiaire : ' . $request->request->get('from') . "\n\n" . $body;

        $message->setBody($body);

        $ret = $this->container->get('mailer')->send($message);

        return array('sent' => $ret !== 0);
    }
}
