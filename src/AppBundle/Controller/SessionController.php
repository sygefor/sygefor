<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inscription;
use AppBundle\Utils\EvaluationSheet;
use AppBundle\Entity\Session\Session;
use AppBundle\Entity\Session\Participation;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Evaluation\EvaluatedTheme;
use AppBundle\Entity\Evaluation\NotedCriterion;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use Sygefor\Bundle\CoreBundle\Entity\Term\PresenceStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sygefor\Bundle\CoreBundle\Controller\AbstractSessionController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\SatisfiesParentSecurityPolicy;

/**
 * @Route("/training/session")
 */
class SessionController extends AbstractSessionController
{
    protected $sessionClass = Session::class;
    protected $participationClass = Participation::class;

    /**
     * @Route("/create/{training}", requirements={"id" = "\d+"}, name="session.create", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="training", permissions="EDIT")
     * @ParamConverter("training", class="SygeforCoreBundle:AbstractTraining", options={"id" = "training"})
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     * @SatisfiesParentSecurityPolicy()
     */
    public function createAction(Request $request, AbstractTraining $training)
    {
        /** @var Session $session */
        $session = new $this->sessionClass();
        $session->setTraining($training);
        $form = $this->createForm($session::getFormType(), $session);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $newModule = $session->getNewModule();
                if ($newModule  && $session->getNewModule()->getName()) {
                    $em->persist($newModule);
                    $newModule->setTraining($session->getTraining());
                    $session->setModule($newModule);
                    $session->setNewModule(null);
                }

                $em->persist($session);
                $training->updateTimestamps();
                $em->flush();
            }
        }

        return array('form' => $form->createView(), 'training' => $session->getTraining(), 'session' => $session);
    }

    /**
     * This action attach a form to the return array when the user has the permission to edit the training.
     *
     * @Route("/{id}/evaluation", requirements={"id" = "\d+"}, name="session.evaluation", options={"expose"=true}, defaults={"_format" = "json"})
     * @SecureParam(name="session", permissions="VIEW")
     * @Rest\View(serializerGroups={"Default", "session"}, serializerEnableMaxDepthChecks=true)
     */
    public function evaluationAction(Session $session)
    {
        $evaluations = array();
        $nbrEvaluations = $nbrPresentInscription = 0;

        /** @var Inscription $inscription */
        foreach ($session->getInscriptions() as $inscription) {
            if ($inscription->getPresenceStatus() && $inscription->getPresenceStatus()->getStatus() === PresenceStatus::STATUS_PRESENT) {
                ++$nbrPresentInscription;
            }

            if (!$inscription->getEvaluation()) {
                continue;
            }
            $values = [];
            $evaluation = $inscription->getEvaluation();
            /** @var EvaluatedTheme $theme */
            foreach ($evaluation->getThemes() as $theme) {
                /** @var NotedCriterion $criterion */
                foreach ($theme->getCriteria() as $criterion) {
                    $values[$theme->getTheme()->getName()][$criterion->getCriterion()->getName()] = $criterion->getNote();
                }
                $values[$theme->getTheme()->getName()]['comments'] = $theme->getComments() ?: 'Pas de commentaire';
            }
            $values['Points forts'] = $evaluation->getGoodPoints() ?: 'Pas de commentaire';
            $values['Points améliorables'] = $evaluation->getBadPoints() ?: 'Pas de commentaire';
            $values['Suggestions'] = $evaluation->getSuggestions() ?: 'Pas de commentaire';
            $evaluations[] = $values;
            ++$nbrEvaluations;
        }

        $average = array();
        $comments = array();
        foreach ($evaluations as $evaluation) {
            foreach ($evaluation as $themeName => $criterion) {
                if (is_array($criterion)) {
                    if (!isset($average[$themeName])) {
                        $average[$themeName] = [];
                    }
                    foreach ($criterion as $criteriaName => $note) {
                        if (is_int($note)) {
                            if (!isset($average[$themeName][$criteriaName])) {
                                $average[$themeName][$criteriaName] = [];
                            }
                            $average[$themeName][$criteriaName][] = $note;
                        } else {
                            $comments[$themeName][] = $criterion['comments'];
                        }
                    }
                } else {
                    $comments[$themeName][] = $criterion;
                }
            }
        }

        foreach ($average as $theme => $criterion) {
            foreach ($criterion as $criteria => $notes) {
                $score = 0;
                foreach ($notes as $note) {
                    $score += $note;
                }
                $average[$theme][$criteria] = $score / count($average[$theme][$criteria]);
            }
        }

        return array(
            'evaluations' => array(
                'summary' => $average,
                'comments' => $comments,
            ),
            'nbrEvaluation' => $nbrEvaluations,
            'nbrPresentInscription' => $nbrPresentInscription,
        );
    }

    /**
     * @Route("/{id}/evaluations", requirements={"id" = "\d+"}, name="session.evaluations", options={"expose"=true}, defaults={"_format" = "xls"}, requirements={"_format"="csv|xls|xlsx"})
     * @Method("GET")
     * @ParamConverter("session", class="AppBundle:Session\Session")
     * @SecureParam(name="session", permissions="VIEW")
     */
    public function evaluationExportAction(Session $session)
    {
        $es = new EvaluationSheet($this->container, $this->get('phpexcel'), $session);

        return $es->getResponse();
    }

    /**
     * Clone participations, inscriptions and materials.
     *
     * @param Session $session
     * @param Session $cloned
     * @param $inscriptions
     * @param $inscriptionManagement
     */
    protected function cloneSessionArrayCollections($session, $cloned, $inscriptions, $inscriptionManagement)
    {
        $em = $this->getDoctrine()->getManager();

        // clone participations
        /** @var Participation $participation */
        foreach ($session->getParticipations() as $participation) {
            /** @var Participation $newParticipation */
            $newParticipation = new $this->participationClass();
            $newParticipation->setSession($cloned);
            $newParticipation->setTrainer($participation->getTrainer());
            $newParticipation->setOrganization($participation->getTrainer()->getOrganization());
            $newParticipation->setIsOrganization($participation->getTrainer()->getIsOrganization());
            $cloned->addParticipation($newParticipation);
            $em->persist($newParticipation);
        }

        // clone inscriptions
        switch ($inscriptionManagement) {
            case 'copy':
                /** @var Inscription $inscription */
                foreach ($inscriptions as $inscription) {
                    $newInscription = clone $inscription;
                    $newInscription->setSession($cloned);
                    $newInscription->setPresenceStatus(null);
                    $cloned->addInscription($newInscription);
                    $em->persist($newInscription);
                }
                break;
            case 'move':
                /** @var Inscription $inscription */
                foreach ($inscriptions as $inscription) {
                    $session->removeInscription($inscription);
                    $inscription->setSession($cloned);
                    $cloned->addInscription($inscription);
                }
                break;
            default:
                break;
        }

        // clone duplicate materials
        $tmpMaterials = $session->getMaterials();
        if (!empty($tmpMaterials)) {
            foreach ($tmpMaterials as $material) {
                $newMat = clone $material;
                $cloned->addMaterial($newMat);
            }
        }
    }
}
