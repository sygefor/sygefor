<?php

namespace Sygefor\Bundle\CoreBundle\Controller;


use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Form\Type\OrganizationType;
use Sygefor\Bundle\TrainingBundle\Entity\Training\SingleSessionTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class CoreController extends Controller
{
    /**
     * @Route("/", name="core.index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/search", name="core.search", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     *
     * @todo : blaise, security
     */
    public function searchAction(Request $request)
    {
        $search = $this->get('sygefor.search');
        $search->handleRequest($request);

        return $search->search();
    }

    /**
     * @Route("/entity", name="core.entity", options={"expose"=true}, defaults={"_format" = "json"})
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     */
    public function entityAction(Request $request)
    {
        // retrieve the entity
        $em = $this->getDoctrine()->getManager();
        $class = $request->get('class');
        $id = $request->get('id');
        $entity = $em->getRepository($class)->find($id);
        if (!$entity) {
            throw new NotFoundHttpException();
        }

        // security
        $security = $this->get('security.context');
        if (!$security->isGranted('VIEW', $entity)) {
            throw new AccessDeniedHttpException();
        }

        // determine the serialization groups
        $groups = array('Default');
        if ($entity instanceof AbstractTraining) {
            $groups[] = 'training';
            if ($entity instanceof SingleSessionTraining) {
                $groups[] = 'session';
            }
        }
        $reflect = new \ReflectionClass($entity);
        $groups[] = strtolower($reflect->getShortName());

        // return the view
        $view = new View($entity);
        $view->setSerializationContext(SerializationContext::create()->setGroups($groups));

        return $view;
    }
}
