<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Sygefor\Bundle\ApiBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Symfony\Component\HttpFoundation\Request;

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
        /** @var Trainer $trainer */
        $trainer = $em->getRepository('SygeforTrainerBundle:Trainer')->find($request->request->get('to'));
        if (!$trainer) {
            throw new InvalidArgumentException('The trainer does not exist');
        }
        if ($trainer->getIsAllowSendMail() === false && $trainer->getIsAllowSendMail() !== null) {
            return ["errors" => "Vous ne pouvez pas envoyer de courriel à ce formateur"];
        }

        $training = null;
        if ($request->request->get('training')) {
            $training = $em->getRepository('SygeforTrainingBundle:Training')->find($request->request->get('training'));
            if (!$training) {
                throw new InvalidArgumentException('The training does not exist');
            }
        }

        if(!filter_var($request->request->get('from'), FILTER_VALIDATE_EMAIL)){
            return ["errors" => "Veuillez renseigner une adresse électronique valide"];
        }

        $message = \Swift_Message::newInstance();
        $message->setFrom($this->container->getParameter('mailer_from'), "Sygefor3");
        $message->setReplyTo($request->request->get('from'));
        $message->setTo($trainer->getEmail());
        $message->setSubject("Message d'un stagiaire - " . $request->request->get('subject'));
        $body = $request->request->get('body');

        // add training link and name
        if ($training) {
            $body = "Stage: https://sygefor.reseau-urfist.fr/#!/training/" . $training->getId() . "?from=true / " . $training->getName() . "\n" . $body;
        }

        // add trainee email
        $body = "Courriel du stagiaire : " . $request->request->get('from') . "\n\n" . $body;

        $message->setBody($body);

        $ret = $this->container->get('mailer')->send($message);

        return array('sent' => $ret !== 0);
    }
}