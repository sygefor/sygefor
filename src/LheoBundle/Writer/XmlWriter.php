<?php

namespace LheoBundle\Writer;

/**
 * Class XmlWriter.
 */
class XmlWriter
{
    /**
     * Generate LHEO from training details.
     *
     * @param $trainings
     * @param $organizationCoordinates
     * @param $degroupAction
     *
     * @return mixed
     */
    public function generateLheoXml($trainings, $organizationCoordinates, $degroupAction = false)
    {
        $rootNode = new \SimpleXMLElement('<lheo xmlns="http://www.lheo.org/2.0"
												 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
												 xsi:schemaLocation="http://www.lheo.org/2.0
												 http://www.lheo.org/2.0/lheo.xsd"></lheo>');
        $offersNode = $rootNode->addChild('offres');

        foreach ($trainings as $training) {
            $training = $training->getHit()['_source'];
            if ($degroupAction) {
                // write a session by training and repeat this for each session
                foreach ($training['sessions'] as $session) {
                    // all training sessions are returned by elasticsearch so we have to check the dateBegin
                    if (strtotime($session['dateBegin']) > time()) {
                        $this->fillFormation($offersNode, $training, array($session), $organizationCoordinates);
                    }
                }
            }
            else {
                // write all training sessions in this training
                $this->fillFormation($offersNode, $training, $training['sessions'], $organizationCoordinates);
            }
        }

        return $rootNode->asXML();
    }

    /**
     * Fill formation details.
     *
     * @param $node
     * @param $training
     * @param $sessions
     * @param $organizationCoordinates
     */
    protected function fillFormation(&$node, $training, $sessions, $organizationCoordinates)
    {
        $internshipNode = $node->addChild('formation');
        $this->fillOrganizationDomain($internshipNode, $organizationCoordinates);
        $this->addChildLimitedString($internshipNode, 'intitule-formation', $training['name'], 1, 255, $cdata = false, $mandatory = true);
        $this->addChildLimitedString($internshipNode, 'objectif-formation', $training['program'], 1, 3000, $cdata = true, $mandatory = true);
        $this->addChildLimitedString($internshipNode, 'resultats-attendus', $training['description'], 1, 3000, $cdata = true, $mandatory = true);
        $this->addChildLimitedString($internshipNode, 'contenu-formation', $training['teachingMethods'], 1, 3000, $cdata = true, $mandatory = true);
        $internshipNode->addChild('certifiante', 0);
        $informationContact = $internshipNode->addChild('contact-formation');
        $this->fillCoordonates($informationContact, $organizationCoordinates, null, $organizationCoordinates['name'], null, null, $organizationCoordinates['phoneNumber'], null,
            $organizationCoordinates['email'], $organizationCoordinates['website']);
        $internshipNode->addChild('parcours-de-formation', 1);
        $internshipNode->addChild('code-niveau-entree', 0);

        foreach ($sessions as $session) {
            // all training sessions are returned by elasticsearch so we have to check the dateBegin
            // need to check the condition if groupAction is setted
            if (strtotime($session['dateBegin']) > time()) {
                $this->fillAction($internshipNode, $training, $session, $organizationCoordinates);
            }
        }

        $this->fillResponsableOrganization($internshipNode, $training, $organizationCoordinates);
        $internshipNode->addChild('identifiant-module', $training['id']);
    }

    /**
     * @param $node
     * @param $training
     * @param $organizationCoordinates
     */
    protected function fillResponsableOrganization(&$node, $training, $organizationCoordinates)
    {
        $responsableOrganization = $node->addChild('organisme-formation-responsable');
        $this->addChildLimitedString($responsableOrganization, 'numero-activite', $organizationCoordinates['activityNumber'], 11, 11, $cdata = false, $mandatory = true);
        $siretTrainingOrganization = $responsableOrganization->addChild('SIRET-organisme-formation');
        $this->addSiret($siretTrainingOrganization, $organizationCoordinates['siret']);
        $this->addChildLimitedString($responsableOrganization, 'nom-organisme', $training['organization']['name'], 1, 250, $cdata = false, $mandatory = true);
        $this->addChildLimitedString($responsableOrganization, 'raison-sociale', $training['organization']['name'], 1, 250, $cdata = false, $mandatory = true);
        $this->addChildLimitedString($responsableOrganization, 'renseignements-specifiques', $organizationCoordinates['specificRenseignements'], 0, 3000);
        $organizationCoordinatesNode = $responsableOrganization->addChild('coordonnees-organisme');
        $this->fillCoordonates($organizationCoordinatesNode, $organizationCoordinates, null, $organizationCoordinates['name'], null, $organizationCoordinates['name'], $organizationCoordinates['phoneNumber'], null,
            $organizationCoordinates['email'], $organizationCoordinates['website']);
        $organizationContact = $responsableOrganization->addChild('contact-organisme');
        $this->fillCoordonates($organizationContact, $organizationCoordinates, null, $organizationCoordinates['name'], null, $organizationCoordinates['name'], $organizationCoordinates['phoneNumber'], null,
            $organizationCoordinates['email'], $organizationCoordinates['website']);
    }

    /**
     * @param $node
     * @param $organizationCoordinates
     */
    protected function fillOrganizationDomain(&$node, $organizationCoordinates)
    {
        $internshipDomainNode = $node->addChild('domaine-formation');
        $this->addChildLimitedString($internshipDomainNode, 'code-FORMACODE', $organizationCoordinates['FORMACODE'], 5, 5);
        $this->addChildLimitedString($internshipDomainNode, 'code-NSF', $organizationCoordinates['NSF'], 3, 3);
        $this->addChildLimitedString($internshipDomainNode, 'code-ROME', $organizationCoordinates['ROME'], 5, 5);
    }

    /**
     * Fill session details.
     *
     * @param $node
     * @param $training
     * @param $session
     * @param $organizationCoordinates
     */
    protected function fillAction(&$node, $training, $session, $organizationCoordinates)
    {
        $action = $node->addChild('action');
        $action->addChild('rythme-formation', $session['schedule']);
        $this->addChildLimitedString($action, 'code-public-vise', '80056', 5, 5, $cdata = false, $cdata = false, $mandatory = true);
        $this->addChildLimitedString($action, 'duree-indicative', strval($session['hourNumber']) . ' heures', 1, 50, $cdata = false, $mandatory = true);
        $action->addChild('niveau-entree-obligatoire', 0);
        $action->addChild('modalites-alternance', 'pas d\'alternance');
        $action->addChild('modalites-enseignement', 0);
        $this->addChildLimitedString($action, 'conditions-specifiques', $training['prerequisites'], 1, 3000, $cdata = false, $mandatory = true);
        $action->addChild('prise-en-charge-frais-possible', '1');
        $formationPlace = $action->addChild('lieu-de-formation');
	    $coordinates = [];
	    foreach ($organizationCoordinates as $key => $value) {
		    $coordinates[$key] = $value;
	    }
	    $place = $session['place'];
	    if ($place && !empty($place['address'])) {
		    $coordinates['name'] = $place['name'];
		    $coordinates['address'] = $place['address'];
		    $coordinates['zip'] = $place['postal'];
		    $coordinates['city'] = $place['city'];
	    }
	    $this->fillCoordonates(
		    $formationPlace,
		    $coordinates,
		    null,
		    $place ? $place['name'] : $organizationCoordinates['name'],
		    null,
		    null,
		    $organizationCoordinates['phoneNumber'],
		    null,
		    null,
		    $organizationCoordinates['email'],
		    $organizationCoordinates['website']
	    );
        $action->addChild('modalites-entrees-sorties', 0);
        $addressInscription = $action->addChild('adresse-inscription');
        $this->fillAddress($addressInscription, $organizationCoordinates['address'], $organizationCoordinates['zip'], $organizationCoordinates['region'], $organizationCoordinates['city']);
        $action->addChild('date-inscription')->addChild('date', $this->formatDate($session['dateBegin']));
        $this->fillSession($action, $session['dateBegin'], $session['dateEnd']);

        $action->addChild('langue-formation', 'fr');
        $action->addChild('frais-restants', $session['price']);
        $action->addChild('date-limite-inscription')->addChild('date', $this->formatDate($session['limitRegistrationDate']));
	    $this->fillTrainerOrganization($action, $session, $organizationCoordinates);
    }

    /**
     * @param $node
     * @param $dateBegin
     * @param $dateEnd
     */
    protected function fillSession(&$node, $dateBegin, $dateEnd)
    {
        $periode = $node->addChild('session')->addChild('periode');
        $periode->addChild('debut', $this->formatDate($dateBegin));
        $periode->addChild('fin', $this->formatDate($dateEnd));
    }

    /**
     * @param $date
     *
     * @return string
     */
    protected function formatDate($date)
    {
        return (new \DateTime($date))->format('Ymd');
    }

    /**
     * @param $node
     * @param $session
     * @param $organizationCoordinates
     */
	protected function fillTrainerOrganization(&$node, $session, $organizationCoordinates)
    {
        $trainerOrganization = $node->addChild('organisme-formateur');
        $trainterSiret       = $trainerOrganization->addChild('SIRET-formateur');
        $this->addSiret($trainterSiret, $organizationCoordinates['siret']);
        $this->addChildLimitedString($trainerOrganization, 'raison-sociale-formateur', $organizationCoordinates['name'], 0, 250, $cdata = false, $mandatory = true);

	    $trainer = (count($session['participations']) > 0 ? $session['participations'][0]['trainer'] : null);
	    $contactTrainer = $trainerOrganization->addChild('contact-formateur');
	    $this->fillCoordonates(
		    $contactTrainer,
		    $organizationCoordinates,
		    $trainer ? $trainer['title'] : null,
		    $trainer ? $trainer['lastName'] : $organizationCoordinates['name'],
		    $trainer ? $trainer['firstName'] : null,
		    null,
		    $organizationCoordinates['phoneNumber'],
		    null,
		    null,
		    $trainer ? $trainer['email'] : $organizationCoordinates['email'],
		    $organizationCoordinates['website']
	    );

	    $potential = $trainerOrganization->addChild('potentiel');
        $this->addChildLimitedString($potential, 'code-FORMACODE', $organizationCoordinates['FORMACODE'], 5, 5);
    }

    /**
     * @param $node
     * @param null $organizationCoordinates
     * @param null $civility
     * @param null $name
     * @param null $firstName
     * @param null $street
     * @param null $fixNumber
     * @param null $mobileNumber
     * @param null $email
     * @param null $webAddress
     */
    protected function fillCoordonates(&$node, $organizationCoordinates = null, $civility = null, $name = null, $firstName = null, $street = null, $fixNumber = null, $mobileNumber = null,
                                    $email = null, $webAddress = null)
    {
        $coordonnates = $node->addChild('coordonnees');
        if ($civility) $this->addChildLimitedString($coordonnates, 'civilite', $civility, 1, 50);
        if ($name) $this->addChildLimitedString($coordonnates, 'nom', $name, 1, 50);
	    if ($firstName) $this->addChildLimitedString($coordonnates, 'prenom', $firstName, 1, 50);
        if ($street) $this->addStreet($coordonnates, $street);
        if ($organizationCoordinates) {
            $this->fillAddress($coordonnates, $organizationCoordinates['address'], $organizationCoordinates['zip'], $organizationCoordinates['region'], $organizationCoordinates['city']);
        }
        if ($fixNumber) {
            $fix = $coordonnates->addChild('telfixe');
            $this->addPhoneNumber($fix, $fixNumber);
        }
        if ($mobileNumber) {
            $mobile = $coordonnates->addChild('portable');
            $this->addPhoneNumber($mobile, $mobileNumber);
        }
        if ($email) $this->addChildLimitedString($coordonnates, 'courriel', $email, 3, 160);
        if ($webAddress) {
            $web = $coordonnates->addChild('web');
            $this->addChildLimitedString($web, 'urlweb', $webAddress, 3, 400);
        }
    }

    /**
     * @param $node
     * @param null $street
     * @param null $zip
     * @param null $region
     * @param null $city
     */
    protected function fillAddress(&$node, $street = null, $zip = null, $region = null, $city = null)
    {
        $address = $node->addChild('adresse');
        if ($street) $this->addStreet($address, $street);
        if ($zip) $this->addChildLimitedString($address, 'codepostal', $zip, 1, 50);
        if ($city) $this->addChildLimitedString($address, 'ville', $city, 1, 50);
        if ($region) $this->addChildLimitedString($address, 'region', strval($region), 2, 2);
        $address->addChild('pays', 'FR');
    }

    /**
     * @param $node
     * @param $child
     * @param $value
     * @param $lowerLimit
     * @param $highLimit
     * @param bool   $cdata
     * @param bool   $mandatory
     * @param string $replacement
     */
    protected function addChildLimitedString(&$node, $child, $value, $lowerLimit, $highLimit, $cdata = false, $mandatory = false, $replacement = 'Non renseigné')
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
    protected function addCdata(&$node, $child, $data)
    {
        $nChild = $node->addChild($child);
        $n      = dom_import_simplexml($nChild);
        $doc    = $n->ownerDocument;
        $n->appendChild($doc->createCDATASection($data));
    }

    /**
     * @param $node
     * @param $value
     */
    protected function addSiret(&$node, $value)
    {
        $this->addChildLimitedString($node, 'SIRET', $value, 14, 14);
    }

    /**
     * @param $node
     * @param $value
     */
    protected function addPhoneNumber(&$node, $value)
    {
        $this->addChildLimitedString($node, 'numtel', $value, 1, 25);
    }

    /**
     * @param $node
     * @param $value
     */
    protected function addStreet(&$node, $value)
    {
        $this->addChildLimitedString($node, 'ligne', $value, 1, 50);
    }

    /**
     * @param $value
     * @param $limit
     *
     * @return string
     */
    protected function limitStringSize($value, $limit)
    {
        return substr($value, 0, $limit);
    }
}
