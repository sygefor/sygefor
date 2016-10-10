<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 9/15/16
 * Time: 11:00 AM
 */

namespace Sygefor\Bundle\FrontBundle\Controller;


use Elastica\Filter\BoolAnd;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query;
use Sygefor\Bundle\CoreBundle\Controller\BatchOperationController;
use Sygefor\Bundle\FrontBundle\Form\FilterRegistrationType;
use Sygefor\Bundle\FrontBundle\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="front.account")
     *
     * @return RedirectResponse
     */
    public function accountAction(Request $request)
    {
        $user = $this->getUser();
        if ($user) {
            if ($user->getIsActive()) {
                // redirect user to registrations pages
                $url = $this->generateUrl('front.account.registrations');
            }
            else {
                return $this->redirectToRoute('front.account.logout', array('return' => $this->generateUrl('front.public.index', array('shibboleth' => 1, 'error' => 'activation'))));
            }
        }
        else {
            // redirect user to registration form
            $url = $this->generateUrl('front.account.register');
        }

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     *
     * @Route("/profile", name="front.account.profile")
     * @Template("@SygeforFront/Account/profile/profile.html.twig")
     *
     * @return array
     */
    public function profileAction(Request $request)
    {
        $form = $this->createForm(new ProfileType($this->get('sygefor_core.access_right_registry')), $this->getUser());
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Votre profil a été mis à jour.');
            }
        }

        return array('user' => $this->getUser(), 'form' => $form->createView());
    }

    /**
     * @param Request $request
     * @param string $return
     *
     * @Route("/logout/{return}", name="front.account.logout", requirements={"return" = ".+"})
     *
     * @return array
     */
    public function logoutAction(Request $request, $return = null)
    {
        $this->get('security.context')->setToken(null);
        $this->get('request')->getSession()->invalidate();

        return $this->redirect($this->get('shibboleth')->getLogoutUrl($request, $return ? $return : $this->generateUrl('front.public.index')));
    }

    /**
     * @param FormInterface|null $form
     * @param string $typeSubmition
     * @param int $page
     * @return array
     */
    protected function getInstitutionRegistrations(FormInterface $form = null, $typeSubmition = null, $page = null)
    {
        $search = $this->get('sygefor_inscription.search');
        if ($page) {
            $search->setPage($page);
        }
        $filters = new BoolAnd();
        $emailFilter = new Term(array('institution.trainingCorrespondents.email' => $this->getUser()->getEmail()));
        $filters->addFilter($emailFilter);

        if ($form) {
            $from = $form->get('createdFrom')->getData();
            $to = $form->get('createdTo')->getData();
            $trainee = $form->get('trainee')->getData();
            $training = $form->get('training')->getData();
            $inscriptionStatus = $form->get('inscriptionStatus')->getData();

            if ($trainee) {
                $traineeFilter = new Term(array('trainee.id' => $trainee->getId()));
                $filters->addFilter($traineeFilter);
            }
            if ($training) {
                $trainingFilter = new Term(array('session.training.id' => $training->getId()));
                $filters->addFilter($trainingFilter);
            }
            if ($inscriptionStatus) {
                $inscriptionStatusFilter = new Term(array('inscriptionStatus.id' => $inscriptionStatus->getId()));
                $filters->addFilter($inscriptionStatusFilter);
            }
            if ($from || $to) {
                $createdAtFilterParts = array();
                if ($from) {
                    $createdAtFilterParts["from"] = $from->format('Y-m-d');
                }
                if ($to) {
                    $createdAtFilterParts["to"] = $to->format('Y-m-d');
                }
                $createdAtFilter = new Range('createdAt', $createdAtFilterParts);
                $filters->addFilter($createdAtFilter);
            }
        }

        if ($typeSubmition === "csv") {
            $search->setPage(1);
            $search->setSize(99999);
        }

        $search->addFilter('filters', $filters);
        $search->addSort('createdAt', 'desc');
        $search->addSort('session.dateBegin', 'desc');

        return $search->search();
    }
}