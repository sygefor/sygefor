<?php
namespace Sygefor\Bundle\TrainerBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityNotFoundException;
use Elastica\Suggest\Phrase;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TrainerBundle\Entity\Participation;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\TrainerBundle\Form\ChangeOrganizationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TrainerController
 * @package Sygefor\Bundle\TrainerBundle\Controller
 * @Route("/trainer")
 */

class TrainerController extends Controller
{
    /**
     * @Route("/search", name="trainer.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        /** @var SearchService $search */
        $search = $this->get('sygefor_trainer.search');
        $search->handleRequest($request);

        // security check
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainer.rights.trainer.all.view")) {
            $search->addTermFilter("organization.id", $this->getUser()->getOrganization()->getId());
        }

        return $search->search();
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="trainer.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="trainer", permissions="VIEW")
     * @ParamConverter("trainer", class="SygeforTrainerBundle:Trainer", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Trainer $trainer, Request $request)
    {
        if ($this->get("security.context")->isGranted('EDIT', $trainer)) {
            $form = $this->createForm('trainer', $trainer);
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($trainer);
                    $em->flush();
                }
            }
            return array('form' => $form->createView(), 'trainer' => $trainer);
        }

        return array('trainer' => $trainer);
    }

    /**
     * @Route("/create", name="trainer.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Request $request)
    {
        $trainer = new Trainer();
        $trainer->setOrganization($this->getUser()->getOrganization());

        //trainer can't be created if user has no rights for it
        if (!$this->get("security.context")->isGranted('CREATE', $trainer)) {
            throw new AccessDeniedException("Action non autorisée");
        }

        $form = $this->createForm('trainer', $trainer);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($trainer);
                $em->flush();
                return array('trainer' => $trainer);
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{session}/add", name="trainer.add", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "session"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function addParticipationAction(Session $session, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $notBlank = new NotBlank(array( 'message' => 'Vous devez sélectionner un formateur.'));
        //using dummy local validation group in order to take into account only locally defined constraint(s)
        $notBlank->addImplicitGroupName('session_add');

        $form = $this->createFormBuilder(null, array('validation_groups' => array('session_add')))
            ->add('trainer', 'entity_hidden', array(
                'label' => 'Formateur',
                'class' => 'SygeforTrainerBundle:Trainer',
                'constraints' => $notBlank
              ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $trainer = $data['trainer'];

                $participation = new Participation();
                $participation->setSession($session);
                $participation->setTrainer($trainer);
                if ($session->getParticipations()->contains($participation)) {
                    $form->get('trainer')->addError(new FormError('Ce formateur est déjà associé à cet évènement.'));
                }
                else {
                    $session->addParticipation($participation);
                    $session->updateTimestamps();
                    $em->persist($participation);
                    $em->flush();
                    return array('participation' => $participation);
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{session}/remove/{participation}", name="trainer.remove", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "session"})
     * @ParamConverter("participation", class="SygeforTrainerBundle:Participation", options={"id" = "participation"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function removeParticipationAction(Session $session, Participation $participation)
    {
        $this->getDoctrine()->getManager()->remove($participation);
        $this->getDoctrine()->getManager()->flush();

        return null;
    }

    /**
     * @Route("/{id}/changeorg", name="trainer.changeorg", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="trainer", permissions="EDIT")
     * @ParamConverter("trainer", class="SygeforTrainerBundle:Trainer", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function changeOrganizationAction(Trainer $trainer, Request $request)
    {
        // security check
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainer.rights.trainer.all.update")) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ChangeOrganizationType(), $trainer);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{id}/remove", name="trainer.delete", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="trainer", permissions="DELETE")
     * @ParamConverter("trainer", class="SygeforTrainerBundle:Trainer", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainer"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(Trainer $trainer)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($trainer);
        $em->flush();
        $this->get('fos_elastica.index')->refresh();

        return array();
    }
}
