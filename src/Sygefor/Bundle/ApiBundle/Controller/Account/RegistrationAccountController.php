<?php
namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
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
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sygefor\Bundle\ApiBundle\Controller\SecurityController;
use Sygefor\Bundle\ApiBundle\Form\Type\ProfileType;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeArray;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeRepository;
use Sygefor\Bundle\TraineeBundle\Form\ApiRegisterType;
use Sygefor\Bundle\TraineeBundle\Form\ArrayTraineeType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeArrayType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * This controller regroup actions related to registration
 *
 * @package Sygefor\Bundle\TraineeBundle\Controller
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class RegistrationAccountController extends Controller
{
    /**
     * Checkout registrations cart
     *
     * @Route("/checkout", name="api.account.checkout", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.inscription"})
     * @Method("POST")
     */
    public function checkoutAction(Request $request)
    {
        /** @var Trainee $trainee */
        $trainee = $this->getUser();

        $sessions = $request->get('sessions');
        if(!$sessions) {
            throw new BadRequestHttpException('You must provide a list of session id.');
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository('SygeforTrainingBundle:Session');

        // @todo change it ?
        // query builder
        $qb = $repository->createQueryBuilder('s')
            ->where('s.id = :session')
            ->andWhere('s.registration >= :registration')
            ->setParameter('registration', Session::REGISTRATION_PRIVATE); // limitRegistrationDate is empty OR  >= NOW

        // get all sessions
        foreach ($sessions as $key => $id) {
            /** @var Session $session */
            $session = $qb
                ->setParameter('session', $id)
                ->getQuery()
                ->getOneOrNullResult();
            if (!$session) {
                throw new BadRequestHttpException('This session id is invalid : ' . $id);
            }

            // check registrable
            if (!$session->isRegistrable()) {
                throw new AccessDeniedException('This session is not registrable : ' . $id);
            }

            $sessions[$key] = $session;
        }

        // filter array
        $sessions = array_filter($sessions);

        // create inscriptions
        $inscriptions = array();
        $repository = $em->getRepository('SygeforTraineeBundle:Inscription');
        foreach ($sessions as $session) {
            // try to find any existent inscription for this trainee
            /** @var Inscription $inscription */
            $inscription = $repository->findOneBy(array(
                'session' => $session,
                'trainee' => $trainee
            ));

            // if inscription do not exists OR the trainee desisted
            if (!$inscription || $inscription->getInscriptionStatus()->isMachineName('desist')) {
                if (!$inscription) {
                    // if not, create it
                    $inscription = new Inscription();
                    $inscription->setTrainee($trainee);
                    $inscription->setSession($session);
                }
                $inscription->setInscriptionStatus(null); // reset the inscription status
                $em->persist($inscription);
                $inscriptions[] = $inscription;
            }
        }
        $em->flush();

        // send a recap to the trainee
        $count = count($inscriptions);
        if ($count) {
            $message = \Swift_Message::newInstance()
                ->setFrom($this->container->getParameter('mailer_from'), $trainee->getOrganization()->getName())
                ->setReplyTo($trainee->getOrganization()->getEmail())
                ->setSubject("Votre demande d'inscription a bien été prise en compte.")
                ->setTo($trainee->getEmail())
                ->setBody($this->renderView('SygeforApiBundle:Account/Registration:checkout.txt.twig', array('trainee' => $trainee, 'inscriptions' => $inscriptions)))
            ;

            // gerenate & attach authorization forms
            $forms = $this->getAuthorizationForms($trainee, $inscriptions);
            foreach ($forms as $code => $html) {
                $data = $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array("print-media-type" => null));
                $attachment = \Swift_Attachment::newInstance($data, 'formulaire_' . $code . '.pdf', 'application/pdf');
                $message->attach($attachment);
            }

            $this->get('mailer')->send($message);
        }

        // return created inscriptions
        return array('inscriptions' => $inscriptions);
    }

    /**
     * Registrations
     *
     * @Route("/registrations", name="api.account.registrations", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.inscription"})
     * @Method("GET")
     */
    public function registrationsAction(Request $request)
    {
        /** @var Trainee $trainee */
        $trainee = $this->getUser();
        return $trainee->getInscriptions();
    }

    /**
     * Desist a registration
     *
     * @Route("/registration/{id}/desist", name="api.account.registration.desist", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.inscription"})
     * @Method("POST")
     */
    public function desistAction($id, Request $request)
    {
        $trainee = $this->getUser();

        /** @var EntityManager $em */
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository('SygeforTraineeBundle:Inscription');

        /** @var Inscription $inscription */
        $inscription = $repository->findOneBy(array(
            'id' => $id,
            'trainee' => $trainee
        ));

        if (!$inscription) {
            throw new NotFoundHttpException('Unknown registration.');
        }

        // check date
        if ($inscription->getSession()->getDateBegin() < new \DateTime()) {
            throw new BadRequestHttpException('You cannot desist from a past session.');
        }

        // check status
        if ($inscription->getInscriptionStatus()->getStatus() > InscriptionStatus::STATUS_ACCEPTED) {
            throw new BadRequestHttpException('Your registration has already been rejected.');
        }

        // ok, let's go
        if ($inscription->getInscriptionStatus()->getStatus() == InscriptionStatus::STATUS_PENDING) {
            // if the inscription is pending, just delete it
            $em->remove($inscription);
        } else {
            // else set the status to "Desist"
            $status = $em->getRepository('SygeforTraineeBundle:Term\InscriptionStatus')->findOneBy(array('machineName' => 'desist', 'organization' => null));
            if (!$status) {
                $status = $em->getRepository('SygeforTraineeBundle:Term\InscriptionStatus')->findOneBy(array('machineName' => 'desist', 'organization' => $trainee->getOrganization()));
            }
            $inscription->setInscriptionStatus($status);
        }

        $em->flush();
        return array('desisted' => true);
    }

    /**
     * Download a authorization form
     *
     * @Route("/registration/{ids}/authorization", name="api.account.registration.authorization")
     * @Method("GET")
     */
    public function authorizationAction($ids, Request $request)
    {
        $registrations = explode(",", $ids);
        $trainee = $this->getUser();

        try {
            // get forms
            $forms = $this->getAuthorizationForms($trainee, $registrations);
        } catch(\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        // join forms & return pdf
        $html = join('<div style="page-break-after: always;"></div>', $forms);
        $filename = 'formulaire'.(count($forms) > 1 ? 's' : '').'_autorisation.pdf';
        return new Response(
          $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array("print-media-type" => null)), 200,
          array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"')
        );
    }


    /**
     * Generate authorization forms
     *
     * @param $trainee
     * @param $registrations
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getAuthorizationForms($trainee, $registrations)
    {
        $repository = $this->get('doctrine')->getManager()->getRepository('SygeforTraineeBundle:Inscription');
        $sessionsByOrg = array();
        /** @var Inscription $registration */

        // verify & group sessions by organization
        foreach($registrations as $registration) {
            if(!($registration instanceof Inscription)) {
                $id = (int)$registration;
                $registration = $repository->find($id);
                if(!$registration) {
                    throw new \InvalidArgumentException("The registration identifier is not valid : " . $id);
                }
            }
            if($registration->getTrainee() != $trainee) {
                throw new \InvalidArgumentException("The registration does not belong to the trainee : " . $registration->getId());
            }
            $sessionsByOrg[$registration->getSession()->getTraining()->getOrganization()->getId()][] = $registration->getSession();
        }

        // build pages
        $forms = array();
        foreach($sessionsByOrg as $org => $sessions) {
            // prepare pdf variables
            $organization = $sessions[0]->getTraining()->getOrganization();
            $variables = array(
              "organization" => $organization,
              "trainee" => $trainee,
              "sessions" => $sessions,
            );
            $forms[$organization->getCode()] = $this->renderView("SygeforApiBundle:Account/Registration:authorization.pdf.twig", $variables);
        }

        return $forms;
    }
}
