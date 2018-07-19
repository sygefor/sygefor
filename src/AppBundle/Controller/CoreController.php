<?php

namespace AppBundle\Controller;

use Sygefor\Bundle\CoreBundle\Entity\AbstractTraining;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\View\View;
use Sygefor\Bundle\CoreBundle\Controller\AbstractCoreController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

class CoreController extends AbstractCoreController
{
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
//            if ($entity instanceof SingleSessionTraining) {
//                $groups[] = 'session';
//            }
        }
        $reflect = new \ReflectionClass($entity);
        $groups[] = strtolower($reflect->getShortName());

        // return the view
        $view = new View($entity);
        $view->setSerializationContext(SerializationContext::create()->setGroups($groups));

        return $view;
    }
}
