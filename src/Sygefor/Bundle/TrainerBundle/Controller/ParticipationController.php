<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 09/12/2015
 * Time: 16:26
 */

namespace Sygefor\Bundle\TrainerBundle\Controller;


use Sygefor\Bundle\TrainerBundle\Entity\Participation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityNotFoundException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Sygefor\Bundle\TraineeBundle\Entity\Inscription;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeArray;
use Sygefor\Bundle\TraineeBundle\Form\ArrayTraineeType;
use Sygefor\Bundle\TraineeBundle\Form\InscriptionType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeArrayType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

class ParticipationController extends Controller
{
    /**
     * @Route("/search", name="participation.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "participation"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_trainer.participation.search');
        $search->handleRequest($request);

        // security check : training
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_training.rights.training.all.view")) {
            $search->addTermFilter("session.training.organization.id", $this->getUser()->getOrganization()->getId());
        }

        // security check : trainer
        //if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainer.rights.trainer.all.view")) {
        //    $search->addTermFilter("trainer.organization.id", $this->getUser()->getOrganization()->getId());
        //}

        return $search->search();
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="participation.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="participation", permissions="VIEW")
     * @ParamConverter("participation", class="SygeforTrainerBundle:Participation", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "participation"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Participation $participation, Request $request)
    {
        $return = array("participation" => $participation);
        if($this->get("security.context")->isGranted('EDIT', $participation)) {
            $form = $this->createForm(new ParticipationType(), $participation);
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($participation);
                    $em->flush();
                    return $return;
                }
            }
            $return['form'] = $form->createView();
        }
        return $return;
    }

    /**
     * @Route("/create/{session}", name="participation.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "session"})
     * @Rest\View(serializerGroups={"Default", "participation"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Session $session, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $participation = new Participation() ;
        $participation->setSession($session);
        $form = $this->createForm(new ParticipationType(), $participation);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($participation);
                $em->flush();
                return array('participation' => $participation);
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{id}/remove", name="participation.delete", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="participation", permissions="DELETE")
     * @ParamConverter("participation", class="SygeforTrainerBundle:Participation", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "participation"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(Participation $participation)
    {
        // remove entity
        $em = $this->getDoctrine()->getManager();
        $em->remove($participation);
        $em->flush();
        // es index refresh
        $this->get('fos_elastica.index')->refresh();
        return array();
    }
}
