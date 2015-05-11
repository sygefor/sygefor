<?php
namespace Sygefor\Bundle\TraineeBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityNotFoundException;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\NationalVocabularyInterface;
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

/**
 * Class InscriptionController
 * @package Sygefor\Bundle\TaxonomyBundle\Controller
 * @Route("/trainee/inscription")
 */

class InscriptionController extends Controller
{
    /**
     * @Route("/search", name="inscription.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_trainee.inscription.search');
        $search->handleRequest($request);

        // security check : training
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_training.rights.training.all.view")) {
            $search->addTermFilter("session.training.organization.id", $this->getUser()->getOrganization()->getId());
        }

        // security check : trainee
        //if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainee.rights.trainee.all.view")) {
        //    $search->addTermFilter("trainee.organization.id", $this->getUser()->getOrganization()->getId());
        //}

        return $search->search();
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="inscription.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="inscription", permissions="VIEW")
     * @ParamConverter("inscription", class="SygeforTraineeBundle:Inscription", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Inscription $inscription, Request $request)
    {
        $return = array("inscription" => $inscription);
        if($this->get("security.context")->isGranted('EDIT', $inscription)) {
            $form = $this->createForm(new InscriptionType(), $inscription);
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($inscription);
                    $em->flush();
                    return $return;
                }
            }
            $return['form'] = $form->createView();
        }
        return $return;
    }

    /**
     * @Route("/create/{session}", name="inscription.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="EDIT")
     * @ParamConverter("session", class="SygeforTrainingBundle:Session", options={"id" = "session"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Session $session, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $inscription = new Inscription() ;
        $inscription->setSession($session);
        $inscription->setInscriptionStatus($em->getRepository('SygeforTraineeBundle:Term\InscriptionStatus')->find(1));
        $form = $this->createForm(new InscriptionType(), $inscription);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($inscription);
                $em->flush();
                return array('inscription' => $inscription);
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{id}/remove", name="inscription.delete", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="inscription", permissions="DELETE")
     * @ParamConverter("inscription", class="SygeforTraineeBundle:Inscription", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "inscription"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(Inscription $inscription)
    {
        // remove entity
        $em = $this->getDoctrine()->getManager();
        $em->remove($inscription);
        $em->flush();
        // es index refresh
        $this->get('fos_elastica.index')->refresh();
        return array();
    }
}
