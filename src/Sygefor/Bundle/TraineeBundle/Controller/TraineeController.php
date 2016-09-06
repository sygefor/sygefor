<?php
namespace Sygefor\Bundle\TraineeBundle\Controller;

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
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Sygefor\Bundle\CoreBundle\Search\SearchService;
use Sygefor\Bundle\TaxonomyBundle\Entity\AbstractTerm;
use Sygefor\Bundle\TaxonomyBundle\Entity\TreeTrait;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyProviderInterface;
use Sygefor\Bundle\TaxonomyBundle\Vocabulary\VocabularyRegistry;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeArray;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeDuplicate;
use Sygefor\Bundle\TraineeBundle\Form\ArrayTraineeType;
use Sygefor\Bundle\TraineeBundle\Form\ChangeDuplicateIgnoranceType;
use Sygefor\Bundle\TraineeBundle\Form\ChangeOrganizationType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeArrayType;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TraineeController
 * @package Sygefor\Bundle\TraineeBundle\Controller
 * @Route("/trainee")
 */
class TraineeController extends Controller
{
    /**
     * @Route("/search", name="trainee.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Security("is_granted('VIEW', 'SygeforTraineeBundle:Trainee')")
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_trainee.search');
        $search->handleRequest($request);

        // security check
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainee.rights.trainee.all.view")) {
            $search->addTermFilter("organization.id", $this->getUser()->getOrganization()->getId());
        }

        return $search->search();
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="trainee.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("trainee", class="SygeforTraineeBundle:Trainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Trainee $trainee, Request $request)
    {
        //access right is checked inside controller, so to be able to send specific error message
        if($this->get("security.context")->isGranted('VIEW', $trainee)) {
            $return = array("trainee" => $trainee);
            if ($this->get("security.context")->isGranted('EDIT', $trainee)) {
                $form = $this->createForm('trainee', $trainee);
                if ($request->getMethod() == 'POST') {
                    $form->handleRequest($request);
                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($trainee);
                        $em->flush();
                    }
                }
                $return['form'] = $form->createView();
            }
            // need to always return form to handle conditionals fields
            return $return;
        } else {
            return new Response(
                '{"message":"Vous n\'avez pas accès aux informations détaillées de cet utilisateur"}',
                403,
                array (
                    "Content-Type"=>"application/json"
                )
            );
        }
    }

    /**
     * @Route("/create", name="trainee.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @Security("is_granted('CREATE', 'SygeforTraineeBundle:Trainee')")
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Request $request)
    {
        $trainee = new Trainee();
        $trainee->setOrganization($this->getUser()->getOrganization());

        //trainee can't be created if user has no rights for it
        if (!$this->get("security.context")->isGranted('CREATE', $trainee)) {
            throw new AccessDeniedException("Action non autorisée");
        }

        $form = $this->createForm('trainee', $trainee);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                //setting a default password and encoding it.
                $password = uniqid();
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($trainee);
                $trainee->setPassword($encoder->encodePassword($password, $trainee->getSalt()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($trainee);
                $em->flush();
                return array('trainee' => $trainee);
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{id}/remove", name="trainee.delete", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="trainee", permissions="DELETE")
     * @ParamConverter("trainee", class="SygeforTraineeBundle:Trainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(Trainee $trainee)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($trainee);
        $em->flush();
        $this->get('fos_elastica.index')->refresh();

        return array();
    }

    /**
     * @Route("/{id}/changepwd", name="trainee.changepwd", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="trainee", permissions="EDIT")
     * @ParamConverter("trainee", class="SygeforTraineeBundle:Trainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function changePasswordAction(Trainee $trainee, Request $request)
    {
        $form = $this->createFormBuilder($trainee)
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'constraints' => array( new Length(array('min' => 6)), new NotBlank()),
                'required' => true,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options'  => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmation'),
            ))
            ->getForm();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // password encoding is handle by TraineeListener
                $trainee->setPassword(null);
                $this->getDoctrine()->getManager()->flush();
                return array('trainee' => $trainee);
            }
        }
        return array('form'=> $form->createView());
    }

    /**
     * @Route("/{id}/changeorg", name="trainee.changeorg", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="trainee", permissions="EDIT")
     * @ParamConverter("trainee", class="SygeforTraineeBundle:Trainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function changeOrganizationAction(Trainee $trainee, Request $request)
    {
        // security check
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainee.rights.trainee.all.update")) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ChangeOrganizationType(), $trainee);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/changeduplicateignorance/{id}", name="trainee.changeduplicateignorance",  options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("traineeDuplicate", class="SygeforTraineeBundle:TraineeDuplicate", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function ignoreDuplicateAction(TraineeDuplicate $traineeDuplicate, Request $request)
    {
        // security check
        if(!$this->get("sygefor_user.access_right_registry")->hasAccessRight("sygefor_trainee.rights.trainee.all.manage_duplicate")) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ChangeDuplicateIgnoranceType(), $traineeDuplicate);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $targetDuplicates = $traineeDuplicate->getTraineeTarget()->getAllDuplicates();
                /**
                 * @var TraineeDuplicate $duplicate
                 */
                $reverseDuplicates =  $targetDuplicates->filter(function($duplicate) use ($traineeDuplicate) {
                    return $duplicate->getTraineeTarget() == $traineeDuplicate->getTraineeSource();
                });

                foreach ($reverseDuplicates as $duplicate) {
                    $this->getDoctrine()->getManager()->merge($duplicate);
                    $duplicate->setIgnored($traineeDuplicate->isIgnored());
                }

                $this->getDoctrine()->getManager()->flush();
            }
        }

        return array('form' => $form->createView());
    }
}
