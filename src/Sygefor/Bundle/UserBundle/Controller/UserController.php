<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 13/03/14
 * Time: 15:18
 */

namespace Sygefor\Bundle\UserBundle\Controller;

use Sygefor\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\Collection;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserController extends Controller
{
    /**
     * @Route("/users", name="user.index")
     * @Template()
     * @Security("is_granted('VIEW', 'SygeforUserBundle:User')")
     */
    public function indexAction()
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository('SygeforUserBundle:User') ;

        $organization = $this->get('security.context')->getToken()->getUser()->getOrganization();
        $hasAccessRightForAll = $this->get('sygefor_user.access_right_registry')->hasAccessRight('sygefor_user.rights.user.all');
        $queryBuilder = $repository->createQueryBuilder('u');

        if(!$hasAccessRightForAll) {
            $queryBuilder->where('u.organization = :organization')
                ->setParameter('organization', $organization);
        }

        $users = $queryBuilder->getQuery()->getResult();
        return array ("users" => $users, 'isAdmin' => $this->getUser()->isAdmin());
    }

    /**
     * @Route("/user/{id}", requirements={"id" = "\d+"}, name="user.view", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @SecureParam(name="user", permissions="VIEW")
     * @ParamConverter("user", class="SygeforUserBundle:User", options={"id" = "id"})
     */
    public function viewAction(User $user, Request $request)
    {
        return $user;
    }

    /**
     * @Route("/user/add", name="user.add")
     * @Template("SygeforUserBundle:User:edit.html.twig")
     * @Security("is_granted('ADD', 'SygeforUserBundle:User')")
     */
    public function addAction(Request $request)
    {
        $user = new User();
        $user->setOrganization($this->getUser()->getOrganization());
        $form = $this->createForm("user", $user);

        if ($request->getMethod() == 'POST') {
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
     * @Route("/user/{id}/edit", requirements={"id" = "\d+"}, name="user.edit", options={"expose"=true})
     * @Rest\View
     * @SecureParam(name="user", permissions="EDIT")
     * @ParamConverter("user", class="SygeforUserBundle:User", options={"id" = "id"})
     */
    public function editAction(User $user, Request $request)
    {
        $oldPwd = $user->getPassword();
        $form = $this->createForm("user", $user);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $newPwd = $form->get('plainPassword')->getData();
                if (isset($newPwd)) {
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $user->setPassword($encoder->encodePassword($newPwd, $user->getSalt()));
                } else {
                    $user->setPassword( $oldPwd );
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
     * @Rest\View
     * @SecureParam(name="user", permissions="EDIT")
     * @ParamConverter("user", class="SygeforUserBundle:User", options={"id" = "id"})
     */
    public function accessRightsAction(User $user, Request $request)
    {
        $builder = $this->createFormBuilder($user);
        $builder->add('accessRights', 'access_rights', array('label' => 'Droits d\'accès'));
        $form = $builder->getForm();

        if ($request->getMethod() == 'POST') {
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
     * @ParamConverter("user", class="SygeforUserBundle:User", options={"id" = "id"})
     */
    public function removeAction(User $user, Request $request)
    {
        if ($request->getMethod() == 'POST') {
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
     * @ParamConverter("loginAsUser", class="SygeforUserBundle:User", options={"id" = "id"})
     * @param User $loginAsUser
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
