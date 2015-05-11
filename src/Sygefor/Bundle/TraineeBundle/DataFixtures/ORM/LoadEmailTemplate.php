<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:39
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TraineeBundle\Entity\Term\EmailTemplate;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPresenceStatus
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadEmailTemplate extends AbstractDataFixture
{

    /**
     * @param ObjectManager $manager
     * @param $name
     * @param $subject
     * @param $body
     * @param null $inscriptionStatus
     * @param null $presenceStatus
     */
    public function loadOneEntry(ObjectManager $manager, $name, $subject, $body, $inscriptionStatus = null, $presenceStatus = null) {
        foreach($manager->getRepository('SygeforCoreBundle:Organization')->findAll() as $organization) {
            $template = new EmailTemplate();
            $template->setOrganization($organization);
            $template->setName($name);
            $template->setSubject($subject);
            $template->setBody($body);
            $template->setInscriptionStatus($inscriptionStatus);
            $template->setPresenceStatus($presenceStatus);
            $manager->persist($template);
        }
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {

        $this->loadOneEntry($manager, "Statut d'inscription : accepté", "Votre demande d'inscription à été acceptée", "[stagiaire.civilite],

Nous avons le plaisir de vous informer que votre demande d'inscription au stage \"[session.formation.nomComplet]\", prévu le [session.dateDebut], a été acceptée.
Vous recevrez, par courrier électronique, une convocation environ deux semaines avant le stage.

Les places étant limitées, nous vous prions instamment de nous informer au plus vite en cas d'indisponibilité de votre part, afin de pouvoir proposer votre place à une autre personne.

Nous rappellons qu'une absence non signalée a un stage entraîne l'annulation des inscriptions à tous les autres stages du programme en cours.

Avec nos cordiales salutations,
L'équipe de l'URFIST.", $manager->find('SygeforTraineeBundle:Term\InscriptionStatus', 4));


        $this->loadOneEntry($manager, "Statut d'inscription : liste d'attente", "Votre demande d'inscription à été mise en attente", "[stagiaire.civilite],

Nous vous informons que votre demande d'inscription au stage \"[session.formation.nomComplet]\", prévu le [session.dateDebut], a été mise en attente, compte tenu du nombre d'inscriptions.
En cas de désistement d'une personne, nous vous inscrirons et vous serez immédiatement prévenu(e).

Les places étant limitées, nous vous prions instamment de nous informer au plus vite en cas de renoncement de votre part, afin de pouvoir proposer votre place à une autre personne.

Avec nos cordiales salutations,
L'équipe de l'URFIST.", $manager->find('SygeforTraineeBundle:Term\InscriptionStatus', 2));


        $this->loadOneEntry($manager, "Statut d'inscription : refus", "Votre demande d'inscription à été refusée", "[stagiaire.civilite],

Nous sommes au regret de vous informer que votre demande d'inscription au stage \"[session.formation.nomComplet]\", prévu le [session.dateDebut], n'a pu être acceptée, ni mise en attente, compte tenu du nombre élevé des inscriptions.

Nous espérons que votre demande pourra être satisfaite lors du prochain programme de formations de l'URFIST.

Avec nos cordiales salutations,
L'équipe de l'URFIST.", $manager->find('SygeforTraineeBundle:Term\InscriptionStatus', 3));

        $this->loadOneEntry($manager, "Statut d'inscription : attente de validation", "Votre demande d'inscription à été prise en compte", "[stagiaire.civilite],

Nous vous informons que votre demande d'inscription à l'événement \"[session.formation.nomComplet]\", prévu le [session.dateDebut], a été prise en compte et sera traitée dans les plus brefs délais.

Vous pouvez suivre l'évolution de votre demande à partir de votre espace personnel sur notre site : http://sygefor.reseau-urfist.fr/#/account

Avec nos cordiales salutations,
L'équipe de l'URFIST.", $manager->find('SygeforTraineeBundle:Term\InscriptionStatus', 1));

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder() {
        return 1;
    }
}
