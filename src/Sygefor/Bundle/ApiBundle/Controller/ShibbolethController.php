<?php
namespace Sygefor\Bundle\ApiBundle\Controller;

use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\OAuth2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/api")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ShibbolethController extends Controller
{
    /**
     * @Route("/shibboleth/auth", name="api.shibboleth.auth")
     */
    public function authAction(Request $request)
    {
        $front_url =  $this->container->getParameter('front_url');
        $user = $this->getUser();
        if($user) {
            // redirect user to login form
            $url = $front_url . '/login?shibboleth=1';
        } else {
            // redirect user to registration form
            $url = $front_url . '/register/organization?shibboleth=1';
        }
        if($request->getQueryString()) {
            $url .= '&'.$request->getQueryString();
        }
        return new RedirectResponse($url);
    }

    /**
     * @Route("/shibboleth/token", name="api.shibboleth.token")
     * @Method("POST")
     */
    public function tokenAction(Request $request)
    {
        $user = $this->getUser();
        if(!$user) {
            throw new AccessDeniedHttpException("Shibboleth session is missing user.");
        }
        $clientId = $request->get('client_id');
        $clientSecret = $request->get('client_secret');
        $generator = $this->get('sygefor_api.oauth.token_generator');
        return $generator->generateTokenResponse($user, $clientId, $clientSecret);
    }

    /**
     * @Route("/shibboleth/attributes", name="api.shibboleth.attributes", defaults={"_format" = "json"})
     * @Rest\View()
     */
    public function attributesAction(Request $request)
    {
        $token = $this->get('security.context')->getToken();
        $attributes = $token->getAttributes();
        $attributes = array_map('utf8_encode', $attributes);
        $attributes['register_data'] = $this->prepareRegisterData($attributes);
        return $attributes;
    }

    /**
     * Prepare register data based on shibboleth attributes
     */
    private function prepareRegisterData($attrs)
    {
        $em = $this->getDoctrine()->getManager();

        $data = array(
          'email' => @$attrs['mail'],
          'lastName' => @$attrs['sn'],
          'firstName' => @$attrs['givenName']
        );

        // title
        if(!empty($attrs['title']) && in_array($attrs['title'], array('Mme', 'Melle'))) {
            $data['title'] = 2;
        } else {
            $data['title'] = 1;
        }

        // phone : mobile first
        $data['phoneNumber'] = isset($attrs['mobile']) ? $attrs['mobile'] : $attrs['telephoneNumber'];

        // postal address
        if(!empty($attrs['postalAddress'])) {
            $address = $this->parseAddress($attrs['postalAddress']);
            if($address) {
                $data['address'] = $address['address'];
                $data['city'] = $address['city'];
                $data['zip'] = $address['zip'];
                $data['department'] = substr($address['zip'], 0, 2);
            }
        }

        // urfist
        if(!empty($data['department'])) {
            $urfists = $em->getRepository('SygeforCoreBundle:Organization')->findAll();
            foreach($urfists as $urfist) {
                if(in_array($data['department'], $urfist->getDepartments())) {
                    $data['organization'] = $urfist->getId();
                    break;
                }
            }
        }

        // institution
        if(!empty($attrs['supannEtablissement']) && !empty($data['organization'])) {
            $rep = $em->getRepository('SygeforTrainingBundle:Term\Institution');
            $name = preg_replace('/^\{[^\}]*\}/', "", $attrs['supannEtablissement']);
            $institution = $rep->findOneBy(array('organization' => $data['organization'], 'name' => $name));
            if(!$institution) {
                $data['otherInstitution'] = $name;
                $institution = $rep->findOneBy(array('organization' => $data['organization'], 'name' => "Autre"));
            }
            $data['institution'] = $institution->getId();
        }

        // Fonction, statut
        if(!empty($attrs['supannRoleGenerique'])) {
            $data['status'] = $attrs['supannRoleGenerique'];
        }


        /*organization:
            department:04
            organization:3
        infos-perso:
            title: 1
            lastName: hnhg
            firstName: ghj
            email: hgj@j.com
        infos-pro:
            publicCategory: 20
            disciplinaryDomain: 38
            teachingCursus: 2
            disciplinary: 40
            institution: 262
            professionalSituation: 21
        infos-contact:
            addressType:0,
            address:"5 impasse des Violettes",
            city:"Thénisy",
            phoneNumber:"+33688301317",
            zip:35000*/

        return $data;
    }

    /**
     * @param $address
     * @return array
     */
    private function parseAddress($address) {
        if (preg_match('/([^$]*)\$(\w{5})\s(.*)/im', $address, $regs)) {
            return array(
                'address' => $regs[1],
                'zip' => $regs[2],
                'city' => $regs[3]
            );
        }
        return false;
    }
}
