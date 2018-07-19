<?php

namespace LheoBundle\Writer;

/**
 * Class RdfWriter.
 */
class RdfWriter
{
    /**
     * @var int
     */
    protected $bnodecpt;

    /**
     * @var \EasyRdf_Graph
     */
    protected $globalGraph;

    /**
     * @var \EasyRdf_Resource
     */
    protected $contactObject;

    /**
     * @param $trainings
     * @param $organizationCoordinates
     *
     * @return mixed
     */
    public function generateLheoRdf($trainings, $organizationCoordinates)
    {
        \EasyRdf_Namespace::set('lheo', 'http://www.conjecto.com/ontology/2015/lheo#');
        $this->globalGraph = new \EasyRdf_Graph();
        $this->bnodecpt    = 0;

        $offers = $this->newObject('lheo:offre-type');
        if ( ! empty($trainings)) {
            $this->contactObject = $this->newObject('lheo:coordonnees-type', '1');
            $this->fillContact($this->contactObject, $organizationCoordinates);

            foreach ($trainings as $training) {
                $esTraining = $training->getHit()['_source'];
                $training   = $this->newObject('lheo:formation-type', $esTraining['id']);
                $this->fillTraining($training, $esTraining, $organizationCoordinates);
                $offers->add('lheo:formation', $training);
            }
        }

        return $offers->getGraph()->serialise('rdfxml');
    }

    /**
     * @param $training
     * @param $esTraining
     * @param $organizationCoordinates
     */
    protected function fillTraining($training, $esTraining, $organizationCoordinates)
    {
        $this->fillOrganizationDomain($training, $organizationCoordinates);
        $training->add('lheo:intitule-formation', $esTraining['name']);
        $training->add('lheo:objectif-formation', $this->addCdata($esTraining['program']));
        $training->add('lheo:resultats-attendus', $this->addCdata($esTraining['description']));
        $training->add('lheo:contenu-formation', $this->addCdata($esTraining['teachingMethods']));
        $training->add('lheo:certifiante', 0);
        $contact = $this->newObject('lheo:contact-formation-type');
        $contact->add('lheo:coordonnees', $this->contactObject);
        $training->add('lheo:contact-formation', $contact);
        $training->add('lheo:parcours-de-formation', 1);
        $training->add('lheo:code-niveau-entree', 0);

        foreach ($esTraining['sessions'] as $esSession) {
            // all training sessions are returned by elasticsearch so we have to check the dateBegin
            if (strtotime($esSession['dateBegin']) > time()) {
                $session = $this->newObject('lheo:action-type', $esSession['id']);
                $this->fillSession($session, $esTraining, $esSession, $organizationCoordinates);
                $training->add('lheo:action', $session);
            }
        }

        $this->fillResponsableOrganization($training, $esTraining, $organizationCoordinates);
        $training->add('lheo:identifiant-module', $esTraining['id']);
    }

    /**
     * @param $action
     * @param $esTraining
     * @param $esSession
     * @param $organizationCoordinates
     */
    protected function fillSession($action, $esTraining, $esSession, $organizationCoordinates)
    {
        $action->add('lheo:rythme-formation', $esSession['schedule']);
        $action->add('lheo:code-public-vise', '80056');
        $action->add('lheo:duree-indicative', strval($esSession['hourNumber']) . ' heures');
        $action->add('lheo:niveau-entree-obligatoire', 0);
        $action->add('lheo:modalites-alternance', 'pas d\'alternance');
        $action->add('lheo:modalites-enseignement', 0);
        $action->add('lheo:conditions-specifiques', $esTraining['prerequisites']);
        $action->add('lheo:prise-en-charge-frais-possible', '1');
        $formationPlace = $this->newObject('lheo:lieu-de-formation-type');
        $formationPlace->add('lheo:coordonnees', $this->contactObject);
        $action->add('lheo:lieu-de-formation', $formationPlace);
        $action->add('lheo:modalites-entrees-sorties', 0);
        $addressInscription = $this->newObject('lheo:adresse-inscription-type');
        $addressInscription->add('lheo:coordonnees', $this->contactObject);
        $action->add('lheo:adresse-inscription', $addressInscription);
        $inscriptionDate = $this->newObject('lheo:date-inscription-type');
        $inscriptionDate->add('lheo:date', $this->formatDate($esSession['dateBegin']));
        $action->add('lheo:date-inscription', $inscriptionDate);
        $session = $this->newObject('lheo:session-type');
        $periode = $this->newObject('lheo:periode-type');
        $periode->add('lheo:debut', $esSession['dateBegin']);
        $periode->add('lheo:fin', $esSession['dateEnd']);
        $session->add('lheo:periode', $periode);
        $action->add('lheo:session', $session);
        $action->add('lheo:langue-formation', 'fr');
        $action->add('lheo:frais-restants', $esSession['price']);
        $inscriptionDate = $this->newObject('lheo:date-limite-inscription-type');
        $inscriptionDate->add('lheo:date', $this->formatDate($esSession['limitRegistrationDate']));
        $action->add('lheo:date-limite-inscription', $inscriptionDate);
        $this->fillTrainerOrganization($action, $organizationCoordinates);
    }

    /**
     * @param $training
     * @param array $organizationCoordinates
     */
    protected function fillOrganizationDomain($training, array $organizationCoordinates)
    {
        $domaine = $this->newObject('lheo:domaine-formation-type');
        $domaine->add('lheo:code-FORMACODE', $organizationCoordinates['FORMACODE']);
        $domaine->add('lheo:code-NSF', $organizationCoordinates['NSF']);
        $domaine->add('lheo:code-ROME', $organizationCoordinates['ROME']);
        $training->add('lheo:domaine-formation', $domaine);
    }

    /**
     * @param $action
     * @param $organizationCoordinates
     */
    protected function fillTrainerOrganization($action, $organizationCoordinates)
    {
        $trainerOrganization = $this->newObject('lheo:organisme-formateur-type');
        $trainterSiret       = $this->newObject('lheo:SIRET-formateur-type');
        $trainterSiret->add('lheo:siret', $organizationCoordinates['siret']);
        $trainerOrganization->add('lheo:SIRET-formateur', $trainterSiret);
        $trainerOrganization->add('lheo:raison-sociale-formateur', $organizationCoordinates['name']);
        $contactTrainer = $this->newObject('lheo:contact-formateur-type');
        $contactTrainer->add('lheo:coordonnees', $this->contactObject);
        $trainerOrganization->add('lheo:contact-formateur', $contactTrainer);
        $potential = $this->newObject('lheo:potentiel-type');
        $potential->add('lheo:code-FORMACODE', $organizationCoordinates['FORMACODE']);
        $trainerOrganization->add('lheo:potentiel', $potential);
        $action->add('lheo:organisme-formateur', $trainerOrganization);
    }

    /**
     * @param $training
     * @param $esTraining
     * @param $organizationCoordinates
     */
    protected function fillResponsableOrganization($training, $esTraining, $organizationCoordinates)
    {
        $responsableOrganization = $this->newObject('lheo:organisme-formation-responsable-type');
        $responsableOrganization->add('lheo:numero-activite', $organizationCoordinates['activityNumber']);

        $siretTrainingOrganization = $this->newObject('lheo:SIRET-organisme-formation-type');
        $siretTrainingOrganization->add('lheo:siret', $organizationCoordinates['siret']);
        $responsableOrganization->add('lheo:SIRET-organisme-formation', $siretTrainingOrganization);

        $responsableOrganization->add('lheo:nom-organisme', $esTraining['organization']['name']);
        $responsableOrganization->add('lheo:raison-sociale', $esTraining['organization']['name']);
        $responsableOrganization->add('lheo:renseignements-specifiques', $organizationCoordinates['specificRenseignements']);

        $contactOrganization = $this->newObject('lheo:coordonnees-organisme-type');
        $contactOrganization->add('lheo:coordonnees', $this->contactObject);
        $responsableOrganization->add('lheo:coordonnees-organisme', $contactOrganization);
        $contactOrganization = $this->newObject('lheo:contact-organisme-type');
        $contactOrganization->add('lheo:coordonnees', $this->contactObject);
        $responsableOrganization->add('lheo:contact-organisme', $contactOrganization);
        $training->add('lheo:organisme-formation-responsable', $responsableOrganization);
    }

    /**
     * @param $contactObject
     * @param array $coordinates
     */
    protected function fillContact($contactObject, array $coordinates)
    {
        $arrayKeys = array(
            'lheo:civilite' => 'civilite',
            'lheo:nom'      => 'name',
            'lheo:prenom'   => 'firstName',
            'lheo:courriel' => 'email',
        );

        foreach ($arrayKeys as $key => $value) {
            if (isset($coordinates[$value])) {
                $contactObject->add($key, $coordinates[$value]);
            }
        }

        $arrayKeys = array(
            'lheo:telfixe'  => 'phoneNumber',
            'lheo:portable' => 'mobileNumber',
        );

        foreach ($arrayKeys as $key => $value) {
            if (isset($coordinates[$value])) {
                $tel = $this->newObject('lheo:numtel-type');
                $tel->add('lheo:numtel', $coordinates[$value]);
                $contactObject->add($key, $tel);
            }
        }

        $arrayKeys = array(
            'lheo:web' => 'website',
        );

        foreach ($arrayKeys as $key => $value) {
            if (isset($coordinates[$value])) {
                $urlweb = $this->newObject('lheo:urlweb-type');
                $urlweb->add('lheo:urlweb', $coordinates[$value]);
                $contactObject->add($key, $urlweb);
            }
        }

        $arrayKeys = array(
            'lheo:ligne'              => 'address',
            'lheo:codepostal'         => 'zip',
            'lheo:ville'              => 'city',
            'lheo:departement'        => 'departement',
            'lheo:code-INSEE-commune' => 'code-INSEE-commune',
            'lheo:code-INSEE-canton'  => 'code-INSEE-canton',
            'lheo:region'             => 'region',
            'lheo:pays'               => 'pays',
            'lheo:geolocalisation'    => 'geolocalisation',
        );

        $address = $this->newObject('lheo:adresse-type');
        foreach ($arrayKeys as $key => $value) {
            if (isset($coordinates[$value])) {
                $address->add($key, $coordinates[$value]);
            }
        }
        $contactObject->add('lheo:adresse', $address);
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
     * @param $rdfType
     * @param null   $id
     * @param string $typeObject
     *
     * @return mixed
     */
    protected function newObject($rdfType, $id = null, $typeObject = 'EasyRdf_Resource')
    {
        $uri = '_bn:' . strval($this->bnodecpt++);
        if ($id) {
            $uri = \EasyRdf_Namespace::expand($rdfType);
            $uri .= '/' . $id;
        }

        $resource = new $typeObject($uri, $this->globalGraph);
        if (is_array($rdfType)) {
            foreach ($rdfType as $type) {
                $resource->addType($type);
            }
        }
        else if (is_string($rdfType)) {
            $resource->addType($rdfType);
        }

        return $resource;
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function addCdata($data)
    {
        return "<![CDATA[$data]]>";
    }
}
