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
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPresenceStatus
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadPresenceStatus extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $id
     * @return PresenceStatus
     */
    public function loadOneEntry(ObjectManager $manager, $id, $name, $status) {
        $presenceStatus = new PresenceStatus();
        $presenceStatus->setId($id);
        $presenceStatus->setName($name);
        $presenceStatus->setStatus($status);
        $manager->persist($presenceStatus) ;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $this->loadOneEntry($manager, 1, "Présent", PresenceStatus::STATUS_PRESENT);
        $this->loadOneEntry($manager, 2, "Absent", PresenceStatus::STATUS_ABSENT);
        $this->loadOneEntry($manager, 3, "Excusé(e)", PresenceStatus::STATUS_ABSENT);
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
