<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 13/03/14
 * Time: 15:18.
 */
namespace Sygefor\Bundle\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sygefor\Bundle\CoreBundle\Entity\User\User;
use Sygefor\Bundle\CoreBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserController extends Controller
{
    /**
     * @Route("/users", name="user.index")
     * @Template()
     * @Security("is_granted('VIEW', 'SygeforCoreBundle:User\User')")
     */
    public function indexAction()
    {
        /** @var EntityManager */
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository('SygeforCoreBundle:User\User');

        $organization = $this->get('security.context')->getToken()->getUser()->getOrganization();
        $hasAccessRightForAll = $this->get('sygefor_core.access_right_registry')->hasAccessRight('sygefor_core.rights.user.all');
        $queryBuilder = $repository->createQueryBuilder('u');

        if (!$hasAccessRightForAll) {
            $queryBuilder->where('u.organization = :organization')
                ->setParameter('organization', $organization);
        }

        $users = $queryBuilder->getQuery()->getResult();

        return array('users' => $users, 'isAdmin' => $this->getUser()->isAdmin());
    }

    /**
     * @param User $user
     *
     * @Route("/user/{id}", requirements={"id" = "\d+"}, name="user.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @SecureParam(name="user", permissions="VIEW")
     * @ParamConverter("user", class="SygeforCoreBundle:User\User", options={"id" = "id"})
     *
     * @return User
     */
    public function viewAction(User $user)
    {
        return $user;
    }

    /**
     * @param Request $request
     *
     * @Route("/user/add", name="user.add")
     * @Template("SygeforCoreBundle:User:edit.html.twig")
     * @Security("is_granted('ADD', 'SygeforCoreBundle:User\User')")
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request)
    {
        $user = new User();
        $user->setOrganization($this->getUser()->getOrganization());
        $form = $this->createForm(UserType::class, $user);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'L\'utilisateur a bien été ajouté.');

                return $this->redirect($this->generateUrl('user.index'));
            }
        }

        return array('form' => $form->createView(), 'user' => $user, 'isAdmin' => $user->isAdmin());
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @Route("/user/{id}/edit", requirements={"id" = "\d+"}, name="user.edit", options={"expose"=true})
     * @Template
     * @SecureParam(name="user", permissions="EDIT")
     * @ParamConverter("user", class="SygeforCoreBundle:User\User", options={"id" = "id"})
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, User $user)
    {
        $oldPwd = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $newPwd = $form->get('plainPassword')->getData();
                if (isset($newPwd)) {
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $user->setPassword($encoder->encodePassword($newPwd, $user->getSalt()));
                }
                else {
                    $user->setPassword($oldPwd);
                }
                $this->getDoctrine()->getManager()->flush();
                $this->get('session')->getFlashBag()->add('success', 'L\'utilisateur a bien été mis à jour.');

                return $this->redirect($this->generateUrl('user.index'));
            }
        }

        return array('form' => $form->createView(), 'user' => $user, 'isAdmin' => $user->isAdmin());
    }

    /**
     * @Route("/user/{id}/access-rights", requirements={"id" = "\d+"}, name="user.access_rights", options={"expose"=true})
     * @Template
     * @SecureParam(name="user", permissions="EDIT")
     * @ParamConverter("user", class="SygeforCoreBundle:User\User", options={"id" = "id"})
     */
    public function accessRightsAction(Request $request, User $user)
    {
        $builder = $this->createFormBuilder($user);
        $builder->add('accessRights', 'access_rights', array('label' => 'Droits d\'accès'));
        $form = $builder->getForm();

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $this->get('session')->getFlashBag()->add('success', "Les droits d'accès ont bien été enregistrés.");

                return $this->redirect($this->generateUrl('user.access_rights', array('id' => $user->getId())));
            }
        }

        return array('form' => $form->createView(), 'user' => $user);
    }

    /**
     * @Route("/user/{id}/remove", requirements={"id" = "\d+"}, name="user.remove")
     * @Template()
     * @SecureParam(name="user", permissions="REMOVE")
     * @ParamConverter("user", class="SygeforCoreBundle:User\User", options={"id" = "id"})
     */
    public function removeAction(Request $request, User $user)
    {
        if ($request->getMethod() === 'POST') {
            if ($user->isAdmin()) {
                $this->get('session')->getFlashBag()->add('error', 'L\'utilisateur actuel est administrateur et ne peut pas être supprimé.');

                return $this->redirect($this->generateUrl('user.edit', array('id' => $user->getId())));
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'L\'utilisateur a bien été supprimé.');

            return $this->redirect($this->generateUrl('user.index'));
        }

        return array('user' => $user);
    }

    /**
     * @Route("/user/{id}/login", requirements={"id" = "\d+"}, name="user.login")
     * @ParamConverter("loginAsUser", class="SygeforCoreBundle:User\User", options={"id" = "id"})
     *
     * @param User $loginAsUser
     *
     * @return RedirectResponse
     */
    public function loginAsAction(User $loginAsUser)
    {
        if (!$this->getUser()->isAdmin()) {
            throw new AccessDeniedHttpException('You can\'t do this action');
        }
        $token = new UsernamePasswordToken($loginAsUser, null, 'user_db', $loginAsUser->getRoles());
        $this->container->get('security.context')->setToken($token);

        return $this->redirect($this->generateUrl('core.index'));
    }
}
