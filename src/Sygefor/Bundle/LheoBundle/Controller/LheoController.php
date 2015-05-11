<?php

namespace Sygefor\Bundle\LheoBundle\Controller;

use Elastica\Filter\Bool;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Nested;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query;
use Elastica\Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\CoreBundle\Entity\Organization;

/**
 * @Route("/api/lheo")
 */
class LheoController extends Controller
{
	/**
	 * @Route("/{code}", requirements={"code" = "\w+"}, name="lheo.trainings")
	 * @Template()
	 * @ParamConverter("organization", class="SygeforCoreBundle:Organization", options={"code" = "code"})
	 */
	public function lheoAction(Organization $organization)
	{
		$results = $this->getTrainings($organization);
		$urfistCoordinates = $this->getUrfistInformations($organization);
		$lheo = $this->generateLheoXml($results, $urfistCoordinates);

		$response = new Response();
		$response->headers->set('Content-type', 'application/xml');
		$response->setContent($lheo);
		return $response;
	}

	public function generateLheoXml($trainings, $urfistCoordinates)
	{
		$rootNode = new \SimpleXMLElement('<lheo xmlns="http://www.lheo.org/2.0"
												 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
												 xsi:schemaLocation="http://www.lheo.org/2.0
												 http://www.lheo.org/2.0/lheo.xsd"></lheo>');
		$offersNode = $rootNode->addChild('offres');

		foreach ($trainings as $training) {
			$training = $training->getHit()['_source'];
			$this->fillFormation($offersNode, $training, $urfistCoordinates);
		}

		return $rootNode->asXML();
	}

	public function getTrainings(Organization $organization)
	{
		/** @var Type $type */
		$type = $this->get('fos_elastica.index.sygefor3.training');

		// construct query
		$queryDSL = new Query();

		$query = new Query\MatchAll();

		$now = (new \DateTime("now", timezone_open('Europe/Paris')))->format('Y-m-d');

		// add filters
		$filters = new BoolAnd();
		$organization = new Term(array('organization.id' => $organization->getId()));
		$internship =   new Term(array('type' => 'internship'));
		$filters->addFilter($organization);
		$filters->addFilter($internship);

		$nestedFilter = new Nested();
		$nestedFilter->setPath('sessions');
		$nestedAnd = new BoolAnd();
		$registration = new Term(array('sessions.registration' => 3));
		$dateBegin = new Range('sessions.dateBegin', array("gte" => $now));
		$nestedAnd->addFilter($registration);
		$nestedAnd->addFilter($dateBegin);
		$nestedFilter->setFilter($nestedAnd);
		$filters->addFilter($nestedFilter);

		// execute query with params
		$queryDSL->setQuery($query);
		$queryDSL->setFilter($filters);
		$results = $type->search($queryDSL);

		return $results->getResults();
	}

	public function getUrfistInformations(Organization $organization)
	{
		$pathJsonFile = file_get_contents("../src/Sygefor/Bundle/LheoBundle/Resources/config/urfistSpecificities.json");
		$urfistCoordinates = json_decode($pathJsonFile, true);
		$urfistCoordinates = $urfistCoordinates[$organization->getCode()];
		$urfistCoordinates['organization'] = $organization;
		return $urfistCoordinates;
	}

	public function fillFormation(&$node, $training, $urfistCoordinates)
	{
        //@todo presence of some of the childs is mandatory
		$internshipNode = $node->addChild('formation');
		$this->fillOrganizationDomain($internshipNode, $urfistCoordinates);
		$this->addChildLimitedString($internshipNode, 'intitule-formation', $training['name'], 1, 255, $cdata = false, $mandatory = true);
		$this->addChildLimitedString($internshipNode, 'objectif-formation', $training['objectives'], 1, 3000, $cdata = true, $mandatory = true);
		$this->addChildLimitedString($internshipNode, 'resultats-attendus', $training['objectives'], 1, 3000, $cdata = true, $mandatory = true);
		$this->addChildLimitedString($internshipNode, 'contenu-formation', $training['program'], 1, 3000, $cdata = true, $mandatory = true);
		$internshipNode->addChild('certifiante', 0);
		$informationContact = $internshipNode->addChild('contact-formation');
		$this->fillCoordonates($informationContact, $urfistCoordinates, null, $urfistCoordinates['name'], null, $urfistCoordinates['name'], $urfistCoordinates['fixNumber'], null,
			$urfistCoordinates['faxNumber'], $urfistCoordinates['email'], $urfistCoordinates['webUrl']);
		$internshipNode->addChild('parcours-de-formation', 1);
		$internshipNode->addChild('code-niveau-entree', 0);

		foreach ($training['sessions'] as $session) {
			$this->fillAction($internshipNode, $training, $session, $urfistCoordinates);
		}

		$this->fillResponsableOrganization($internshipNode, $training, $urfistCoordinates);
		$internshipNode->addChild('identifiant-module', $training['id']);
	}

	public function fillResponsableOrganization(&$node, $training, $urfistCoordinates)
	{
		$responsableOrganization = $node->addChild('organisme-formation-responsable');
		$this->addChildLimitedString($responsableOrganization, 'numero-activite', $urfistCoordinates['activityNumber'], 11, 11, $cdata = false, $mandatory = true);
		$siretTrainingOrganization = $responsableOrganization->addChild('SIRET-organisme-formation');
		$this->addSiret($siretTrainingOrganization, $urfistCoordinates['siret']);
		$this->addChildLimitedString($responsableOrganization, 'nom-organisme', $training['organization']['name'], 1, 250, $cdata = false, $mandatory = true);
		$this->addChildLimitedString($responsableOrganization, 'raison-sociale', $training['organization']['name'], 1, 250, $cdata = false, $mandatory = true);
		$this->addChildLimitedString($responsableOrganization, 'renseignements-specifiques', $urfistCoordinates['specificRenseignements'], 0, 3000);
		$organizationCoordinates = $responsableOrganization->addChild('coordonnees-organisme');
		$this->fillCoordonates($organizationCoordinates, $urfistCoordinates, null, $urfistCoordinates['name'], null, $urfistCoordinates['name'], $urfistCoordinates['fixNumber'], null,
			$urfistCoordinates['faxNumber'], $urfistCoordinates['email'], $urfistCoordinates['webUrl']);
		$organizationContact = $responsableOrganization->addChild('contact-organisme');
		$this->fillCoordonates($organizationContact, $urfistCoordinates, null, $urfistCoordinates['name'], null, $urfistCoordinates['name'], $urfistCoordinates['fixNumber'], null,
			$urfistCoordinates['faxNumber'], $urfistCoordinates['email'], $urfistCoordinates['webUrl']);
	}

	public function fillOrganizationDomain(&$node, $urfistCoordinates)
	{
		$internshipDomainNode = $node->addChild('domaine-formation');
		$this->addChildLimitedString($internshipDomainNode, 'code-FORMACODE', $urfistCoordinates['FORMACODE'], 5, 5);
		$this->addChildLimitedString($internshipDomainNode, 'code-NSF', $urfistCoordinates['NSF'], 3, 3);
		$this->addChildLimitedString($internshipDomainNode, 'code-ROME', $urfistCoordinates['ROME'], 5, 5);
	}

	public function fillAction(&$node, $training, $session, $urfistCoordinates)
	{
		$action = $node->addChild('action');
		$action->addChild('rythme-formation', 'Temps plein');
		$this->addChildLimitedString($action, 'code-public-vise', '80056', 5, 5, $cdata = false, $cdata = false, $mandatory = true);
		$this->addChildLimitedString($action, 'duree-indicative', strval($session['hourDuration']) . " heures", 1, 50, $cdata = false, $mandatory = true);
		$action->addChild('niveau-entree-obligatoire', 0);
		$action->addChild('modalites-alternance', 'pas d\'alternance');
		$action->addChild('modalites-enseignement', 0);
		$this->addChildLimitedString($action, 'conditions-specifiques', $training['prerequisite'], 1, 3000, $cdata = false, $mandatory = true);
		$action->addChild('prise-en-charge-frais-possible', '1');
		$formationPlace = $action->addChild('lieu-de-formation');
		$this->fillCoordonates($formationPlace, $urfistCoordinates, null, $urfistCoordinates['name'], null, $session['place'], $urfistCoordinates['fixNumber'], null, $urfistCoordinates['faxNumber'],
			$urfistCoordinates['email'], $urfistCoordinates['webUrl']);
		$action->addChild('modalites-entrees-sorties', 0);
		$addressInscription = $action->addChild('adresse-inscription');
		$this->fillAddress($addressInscription, $urfistCoordinates['street'], $urfistCoordinates['zip'], $urfistCoordinates['region'], $urfistCoordinates['city']);
		$action->addChild('date-inscription')->addChild('date', $this->formatDate($session['dateBegin']));
		$this->fillSession($action, $session['dateBegin'], $session['dateEnd']);

		$action->addChild('langue-formation', 'fr');
		$action->addChild('frais-restants',	$session['price']);
		$action->addChild('date-limite-inscription')->addChild('date', $this->formatDate($session['limitRegistrationDate']));
		$this->fillTrainerOrganization($action, $urfistCoordinates);
	}

	public function fillSession(&$node, $dateBegin, $dateEnd)
	{
		$periode = $node->addChild('session')->addChild('periode');
		$periode->addChild('debut', $this->formatDate($dateBegin));
		$periode->addChild('fin', $this->formatDate($dateEnd));
	}

	public function formatDate($date)
	{
		return (new \DateTime($date))->format('Ymd');
	}

	public function fillTrainerOrganization(&$node, $urfistCoordinates)
	{
		$trainerOrganization = $node->addChild('organisme-formateur');
		$trainterSiret = $trainerOrganization->addChild('SIRET-formateur');
		$this->addSiret($trainterSiret, $urfistCoordinates['siret']);
		$this->addChildLimitedString($trainerOrganization, 'raison-sociale-formateur', $urfistCoordinates['name'], 0, 250, $cdata = false, $mandatory = true);
		$contactTrainer = $trainerOrganization->addChild('contact-formateur');
		$this->fillCoordonates($contactTrainer, $urfistCoordinates, null, $urfistCoordinates['name'], null, $urfistCoordinates['name'], $urfistCoordinates['fixNumber'], null, $urfistCoordinates['faxNumber'],
			$urfistCoordinates['email'], $urfistCoordinates['webUrl']);
		$potential = $trainerOrganization->addChild('potentiel');
		$this->addChildLimitedString($potential, 'code-FORMACODE', $urfistCoordinates['FORMACODE'], 5, 5);
	}

	public function fillCoordonates(&$node, $urfistCoordinates = null, $civility = null, $name = null, $firstName = null, $street = null, $fixNumber = null, $mobileNumber = null,
	                                $faxNumber = null, $email = null, $webAddress = null)
	{
		$coordonnates = $node->addChild('coordonnees');
		if ($civility) $this->addChildLimitedString($coordonnates, 'civilite', $civility, 1, 50);
		if ($name) $this->addChildLimitedString($coordonnates, 'nom', $name, 1, 50);
		if ($firstName) $this->addChildLimitedString($coordonnates, $firstName, 'value', 1, 50);
		if ($street) $this->addStreet($coordonnates, $street);
		if ($urfistCoordinates) {
			$this->fillAddress($coordonnates, $urfistCoordinates['street'], $urfistCoordinates['zip'], $urfistCoordinates['region'], $urfistCoordinates['city']);
		}
		if ($fixNumber) {
			$fix = $coordonnates->addChild('telfixe');
			$this->addPhoneNumber($fix, $fixNumber);
		}
		if ($mobileNumber) {
			$mobile = $coordonnates->addChild('portable');
			$this->addPhoneNumber($mobile, $mobileNumber);
		}
		if ($faxNumber) {
			$fax = $coordonnates->addChild('fax');
			$this->addPhoneNumber($fax, $faxNumber);
		}
		if ($email) $this->addChildLimitedString($coordonnates, 'courriel', $email, 3, 160);
		if ($webAddress) {
			$web = $coordonnates->addChild('web');
			$this->addChildLimitedString($web, 'urlweb', $webAddress, 3, 400);
		}
	}

	public function fillAddress(&$node, $street = null, $zip = null, $region = null, $city = null)
	{
		$address = $node->addChild('adresse');
		if ($street) $this->addStreet($address, $street);
		if ($zip) $this->addChildLimitedString($address, 'codepostal', $zip, 1, 50);
		if ($city) $this->addChildLimitedString($address, 'ville', $city, 1, 50);
		if ($region) $this->addChildLimitedString($address, 'region', strval($region), 2, 2);
		$address->addChild('pays', 'FR');
	}

    /**
     * @param \SimpleXMLElement $node
     * @param $child
     * @param $value
     * @param $lowerLimit
     * @param $highLimit
     * @param bool $mandatory
     * @param string $replacement
     */
	public function addChildLimitedString(&$node, $child, $value, $lowerLimit, $highLimit, $cdata = false, $mandatory = false, $replacement = "Non renseigné")
	{
        $value = str_replace('&', 'et', $value);
        $value = trim(strip_tags($value));

        if (is_int($value)) {
            $value = intval($value);
        }

        if (empty($value) && $mandatory) {
            $value = $replacement;
        }

        $len = strlen($value);
        if ($len > 0 && $len >= $lowerLimit) {
            if ($cdata) {
                $this->addCdata($node, $child, $this->limitStringSize($value, $highLimit));
            } else {
                $node->$child = $this->limitStringSize($value, $highLimit);
            }

        }
	}

    /**
     * @param \SimpleXMLElement $node
     * @param $child
     * @param $data
     */
    private function addCdata(&$node, $child, $data)
    {
        $nChild = $node->addChild($child);
        $n = dom_import_simplexml($nChild);
        $doc = $n->ownerDocument;
        $n->appendChild($doc->createCDATASection($data));
    }

	public function addSiret(&$node, $value)
	{
		$this->addChildLimitedString($node, 'SIRET', $value, 14, 14);
	}

	public function addPhoneNumber(&$node, $value)
	{
		$this->addChildLimitedString($node, 'numtel', $value, 1, 25);
	}

	public function addStreet(&$node, $value)
	{
		$this->addChildLimitedString($node, 'ligne', $value, 1, 50);
	}

	public function limitStringSize($value, $limit)
	{
		return substr($value, 0, $limit);
	}
}