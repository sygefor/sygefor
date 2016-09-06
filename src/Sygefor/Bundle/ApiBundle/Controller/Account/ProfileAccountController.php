<?php
namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use FOS\RestBundle\View\View;
use Sygefor\Bundle\ApiBundle\Form\Type\ProfileType;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * This controller regroup actions related to account profile
 *
 * @package Sygefor\Bundle\TraineeBundle\Controller
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ProfileAccountController extends Controller
{
    /**
     * Profile
     *
     * @Route("/profile", name="api.account.profile", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.profile"})
     * @Method({"GET", "POST"})
     */
    public function profileAction(Request $request)
    {
        /** @var Trainee $trainee */
        $trainee = $this->getUser();
        if($request->getMethod() == 'POST') {
            $form = $this->createForm(new ProfileType(), $trainee);
            // remove extra fields
            $data = ProfileType::extractRequestData($request, $form);
            // if shibboleth, remove email
//            if($trainee->getShibbolethPersistentId()) { // permit shib users to change email
//                $data['email'] = $trainee->getEmail();
//            }
            // submit
            $form->submit($data, true);
            if($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                return array("updated" => true);
            } else {
                /** @var FormError $error */
                $parser = $this->get('sygefor_api.form_errors.parser');
                return new View(array('errors' => $parser->parseErrors($form)), 422);
            }
        }
        return $trainee;
    }
}
