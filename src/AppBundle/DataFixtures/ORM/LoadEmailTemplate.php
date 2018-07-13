<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:39.
 */

namespace AppBundle\DataFixtures\ORM;

use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTermLoad;
use Sygefor\Bundle\CoreBundle\Entity\Term\EmailTemplate;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\PublipostTemplate;

/**
 * Class LoadPresenceStatus.
 */
class LoadEmailTemplate extends AbstractTermLoad
{
    public static $class = EmailTemplate::class;

    public function getTerms()
    {
        $emailsTemplates = array();
        foreach ($this->organizations as $organization) {
            $emailsTemplates[] = array(
                'name' => "Statut d'inscription : attente de validation",
                'organization' => $organization,
                'subject' => "Votre demande d'inscription à été prise en compte",
                'body' => "<p>[stagiaire.civilite],</p>

<p>Nous vous informons que votre demande d'inscription à l'événement &quot;[session.formation.nom]&quot;, prévu le [session.dateDebut], a été prise en compte et sera traitée dans les plus brefs délais.</p>

<p>Vous pouvez suivre l'évolution de votre demande à partir de votre espace personnel sur notre site : ".$this->container->getParameter('front_host')."/account</p>

<p>Avec nos cordiales salutations,</p>

<p>L'équipe de ".$organization->getName().'.</p>',
                'inscriptionStatus' => $this->manager->find(InscriptionStatus::class, 1),
                'presenceStatus' => null,
            );

            $emailsTemplates[] = array(
                'name' => "Statut d'inscription : liste d'attente",
                'organization' => $organization,
                'subject' => "Votre demande d'inscription à été mise en attente",
                'body' => "<p>[stagiaire.civilite],</p>

<p>Nous vous informons que votre demande d'inscription au stage &quot;[session.formation.nom]&quot;, prévu le [session.dateDebut], a été mise en attente, compte tenu du nombre d'inscriptions.
En cas de désistement d'une personne, nous vous inscrirons et vous serez immédiatement prévenu(e).</p>

<p>Les places étant limitées, nous vous prions instamment de nous informer au plus vite en cas de renoncement de votre part, afin de pouvoir proposer votre place à une autre personne.</p>

<p>Avec nos cordiales salutations,</p>

<p>L'équipe de ".$organization->getName().'.</p>',
                'inscriptionStatus' => $this->manager->find(InscriptionStatus::class, 2),
                'presenceStatus' => null,
            );

            $emailsTemplates[] = array(
                'name' => "Statut d'inscription : refus",
                'organization' => $organization,
                'subject' => "Votre demande d'inscription à été refusée",
                'body' => "<p>[stagiaire.civilite],</p>

<p>Nous sommes au regret de vous informer que votre demande d'inscription au stage &quot;[session.formation.nom]&quot;, prévu le [session.dateDebut], n'a pu être acceptée, ni mise en attente, compte tenu du nombre élevé des inscriptions.</p>

<p>Nous espérons que votre demande pourra être satisfaite lors du prochain programme de formations.</p>

<p>Avec nos cordiales salutations,</p>

<p>L'équipe de ".$organization->getName().'.</p>',
                'inscriptionStatus' => $this->manager->find(InscriptionStatus::class, 3),
                'presenceStatus' => null,
            );

            $emailsTemplates[] = array(
                'name' => "Statut d'inscription : accepté",
                'organization' => $organization,
                'subject' => "Votre demande d'inscription à été acceptée",
                'body' => "<p>[stagiaire.civilite],</p>

<p>Nous avons le plaisir de vous informer que votre demande d'inscription au stage &quot;[session.formation.nom]&quot;, prévu le [session.dateDebut], a été acceptée.
Vous recevrez, par courrier électronique, une convocation environ deux semaines avant le stage.</p>

<p>Les places étant limitées, nous vous prions instamment de nous informer au plus vite en cas d'indisponibilité de votre part, afin de pouvoir proposer votre place à une autre personne.</p>

<p>Nous rappelons qu'une absence non signalée à un stage entraîne l'annulation des inscriptions à tous les autres stages du programme en cours.</p>

<p>Avec nos cordiales salutations,</p>

<p>L'équipe de ".$organization->getName().'.</p>',
                'inscriptionStatus' => $this->manager->find(InscriptionStatus::class, 4),
                'presenceStatus' => null,
            );
        }

        return $emailsTemplates;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
