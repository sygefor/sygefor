<?php

namespace Sygefor\Bundle\TraineeBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\CoreBundle\Form\Type\ChangeOrganizationType;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\TraineeBundle\Form\TraineeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TraineeController.
 *
 * @Route("/trainee")
 */
abstract class AbstractTraineeController extends Controller
{
    /**
     * @var string
     */
    protected $traineeClass = AbstractTrainee::class;

    /**
     * @Route("/search", name="trainee.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Security("is_granted('VIEW', 'SygeforTraineeBundle:AbstractTrainee')")
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     * 
     * @return mixed
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor_trainee.search');
        $search->handleRequest($request);

        // security check
        if( ! $this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_trainee.rights.trainee.all.view')) {
            $search->addTermFilter('organization.id', $this->getUser()->getOrganization()->getId());
        }

        return $search->search();
    }

    /**
     * @Route("/create", name="trainee.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @Security("is_granted('CREATE', 'SygeforTraineeBundle:AbstractTrainee')")
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function createAction(Request $request)
    {
        /** @var AbstractTrainee $trainee */
        $trainee = new $this->traineeClass;
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
                $factory  = $this->get('security.encoder_factory');
                $encoder  = $factory->getEncoder($trainee);
                $trainee->setPassword($encoder->encodePassword($password, $trainee->getSalt()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($trainee);
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'trainee' => $trainee);
    }

    /**
     * @Route("/{id}/view", requirements={"id" = "\d+"}, name="trainee.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("trainee", class="SygeforTraineeBundle:AbstractTrainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function viewAction(Request $request, AbstractTrainee $trainee)
    {
        //access right is checked inside controller, so to be able to send specific error message
        if ( ! $this->get('security.context')->isGranted('VIEW', $trainee)) {
            throw new AccessDeniedException("Vous n'avez pas accès aux informations détaillées de cet utilisateur");
        }

        $form = $this->createForm($trainee::getFormType(), $trainee);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($trainee);
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'trainee' => $trainee);
    }

    /**
     * @Route("/{id}/toggleActivation", requirements={"id" = "\d+"}, name="trainee.toggleActivation", options={"expose"=true}, defaults={"_format" = "json"})
     * @ParamConverter("trainee", class="SygeforTraineeBundle:AbstractTrainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     * @Method("POST")
     */
    public function toggleActivationAction(Request $request, AbstractTrainee $trainee)
    {
        //access right is checked inside controller, so to be able to send specific error message
        if (!$this->get('security.context')->isGranted('EDIT', $trainee)) {
            throw new AccessDeniedException("Vous n'avez pas accès aux informations détaillées de cet utilisateur");
        }

        $trainee->setIsActive(!$trainee->getIsActive());
        $this->getDoctrine()->getManager()->flush();

        return array('trainee' => $trainee);
    }

    /**
     * @Route("/{id}/remove", name="trainee.delete", options={"expose"=true}, defaults={"_format" = "json"})
     * @Method("POST")
     * @SecureParam(name="trainee", permissions="DELETE")
     * @ParamConverter("trainee", class="SygeforTraineeBundle:AbstractTrainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function deleteAction(AbstractTrainee $trainee)
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
     * @ParamConverter("trainee", class="SygeforTraineeBundle:AbstractTrainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function changePasswordAction(Request $request, AbstractTrainee $trainee)
    {
        $form = $this->createFormBuilder($trainee)
            ->add('plainPassword', 'repeated', array(
                'type'            => 'password',
                'constraints'     => array(new Length(array('min' => 6)), new NotBlank()),
                'required'        => true,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options'   => array('label' => 'Mot de passe'),
                'second_options'  => array('label' => 'Confirmation'),
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

    /**
     * @Route("/{id}/changeorg", name="trainee.changeorg", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="trainee", permissions="EDIT")
     * @ParamConverter("trainee", class="SygeforTraineeBundle:AbstractTrainee", options={"id" = "id"})
     * @Rest\View(serializerGroups={"Default", "trainee"}, serializerEnableMaxDepthChecks=true)
     */
    public function changeOrganizationAction(Request $request, AbstractTrainee $trainee)
    {
        // security check
        if ( ! $this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_trainee.rights.trainee.all.update')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ChangeOrganizationType::class, $trainee);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return array('form' => $form->createView(), 'trainee' => $trainee);
    }
}
