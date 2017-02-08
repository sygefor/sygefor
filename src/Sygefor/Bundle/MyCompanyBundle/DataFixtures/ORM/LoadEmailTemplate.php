<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:39.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\TraineeBundle\Entity\Term\EmailTemplate;

/**
 * Class LoadPresenceStatus.
 */
class LoadEmailTemplate extends AbstractTermLoad
{
    static $class = EmailTemplate::class;

    public function getTerms()
    {
        return array(
            array(
                'name'    => "Statut d'inscription : attente de validation",
                'subject' => "Votre demande d'inscription à été prise en compte",
                'body'    => "[stagiaire.civilite],

Nous vous informons que votre demande d'inscription à l'événement \"[session.formation.nom]\", prévu le [session.dateDebut], a été prise en compte et sera traitée dans les plus brefs délais.

Vous pouvez suivre l'évolution de votre demande à partir de votre espace personnel sur notre site : http://front_url.dev/#/account

Avec nos cordiales salutations,
L'équipe de votre Compagnie.",
                'inscriptionStatus' => $this->manager->find('SygeforInscriptionBundle:Term\InscriptionStatus', 1),
                'presenceStatus'    => null,
            ),
            array(
                'name'    => "Statut d'inscription : liste d'attente",
                'subject' => "Votre demande d'inscription à été mise en attente",
                'body'    => "[stagiaire.civilite],

Nous vous informons que votre demande d'inscription au stage \"[session.formation.nom]\", prévu le [session.dateDebut], a été mise en attente, compte tenu du nombre d'inscriptions.
En cas de désistement d'une personne, nous vous inscrirons et vous serez immédiatement prévenu(e).

Les places étant limitées, nous vous prions instamment de nous informer au plus vite en cas de renoncement de votre part, afin de pouvoir proposer votre place à une autre personne.

Avec nos cordiales salutations,
L'équipe de votre Compagnie.",
                'inscriptionStatus' => $this->manager->find('SygeforInscriptionBundle:Term\InscriptionStatus', 2),
                'presenceStatus'    => null,
            ),
            array(
                'name'    => "Statut d'inscription : refus",
                'subject' => "Votre demande d'inscription à été refusée",
                'body'    => "[stagiaire.civilite],

Nous sommes au regret de vous informer que votre demande d'inscription au stage \"[session.formation.nom]\", prévu le [session.dateDebut], n'a pu être acceptée, ni mise en attente, compte tenu du nombre élevé des inscriptions.

Nous espérons que votre demande pourra être satisfaite lors du prochain programme de formations de votre Compagnie.

Avec nos cordiales salutations,
L'équipe de votre Compagnie.",
                'inscriptionStatus' => $this->manager->find('SygeforInscriptionBundle:Term\InscriptionStatus', 3),
                'presenceStatus'    => null,
            ),
            array(
                'name'    => "Statut d'inscription : accepté",
                'subject' => "Votre demande d'inscription à été acceptée",
                'body'    => "[stagiaire.civilite],

Nous avons le plaisir de vous informer que votre demande d'inscription au stage \"[session.formation.nom]\", prévu le [session.dateDebut], a été acceptée.
Vous recevrez, par courrier électronique, une convocation environ deux semaines avant le stage.

Les places étant limitées, nous vous prions instamment de nous informer au plus vite en cas d'indisponibilité de votre part, afin de pouvoir proposer votre place à une autre personne.

Nous rappelons qu'une absence non signalée à un stage entraîne l'annulation des inscriptions à tous les autres stages du programme en cours.

Avec nos cordiales salutations,
L'équipe de votre Compagnie.",
                'inscriptionStatus' => $this->manager->find('SygeforInscriptionBundle:Term\InscriptionStatus', 4),
                'presenceStatus'    => null,
            ),
        );
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    function getOrder()
    {
        return 2;
    }
}
