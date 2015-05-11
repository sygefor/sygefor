<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 02/06/14
 * Time: 14:41
 */

namespace Sygefor\Bundle\TraineeBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadInscriptionStatus
 * @package Sygefor\Bundle\TraineeBundle\DataFixtures\ORM
 */
class LoadInscriptionStatus extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $id
     * @param $name
     * @param $status
     */
    public function loadOneEntry(ObjectManager $manager, $id, $name, $status) {
        $inscriptionStatus = new InscriptionStatus();
        $inscriptionStatus->setId($id);
        $inscriptionStatus->setName($name);
        $inscriptionStatus->setStatus($status);
        $manager->persist($inscriptionStatus) ;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $this->loadOneEntry($manager, 1, "Attente de validation", InscriptionStatus::STATUS_PENDING);
        $this->loadOneEntry($manager, 2, "Liste d'attente", InscriptionStatus::STATUS_WAITING);
        $this->loadOneEntry($manager, 3, "Refusé", InscriptionStatus::STATUS_REJECTED);
        $this->loadOneEntry($manager, 4, "Accepté", InscriptionStatus::STATUS_ACCEPTED);
        $this->loadOneEntry($manager, 5, "Désistement", InscriptionStatus::STATUS_REJECTED);
        $manager->flush();
    }
}
