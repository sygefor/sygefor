<?php

namespace Sygefor\Bundle\MyCompanyBundle\Controller;


use Sygefor\Bundle\MyCompanyBundle\Entity\Participation;
use Sygefor\Bundle\MyCompanyBundle\Entity\Session;
use Sygefor\Bundle\MyCompanyBundle\SpreadSheet\EvaluationSheet;
use Sygefor\Bundle\TrainingBundle\Controller\AbstractSessionController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\SecureParam;

/**
 * @Route("/training/session")
 */
class SessionController extends AbstractSessionController
{
    protected $sessionClass = Session::class;
    protected $participationClass = Participation::class;

    /**
     * @Route("/{id}/evaluations", requirements={"id" = "\d+"}, name="session.evaluations", options={"expose"=true}, defaults={"_format" = "xls"}, requirements={"_format"="csv|xls|xlsx"})
     * @Method("GET")
     * @ParamConverter("session", class="SygeforMyCompanyBundle:Session")
     * @SecureParam(name="session", permissions="VIEW")
     */
    public function evaluationExportAction(Session $session)
    {
        $es = new EvaluationSheet($this->container, $this->get('phpexcel'), $session);

        return $es->getResponse();
    }
}
