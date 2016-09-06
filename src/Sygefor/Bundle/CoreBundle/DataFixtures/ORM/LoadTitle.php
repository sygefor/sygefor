<?php
namespace Sygefor\Bundle\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Term\Title;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTitle
 * @package Sygefor\Bundle\TraineeBundle\DataFixtures\ORM
 */
class LoadTitle extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $name
     * @return Title
     */
    public function loadOneEntry(ObjectManager $manager, $name) {
        $title = new Title();
        $title->setId(++$this->autoId);
        $title->setName($name);
        $manager->persist($title) ;
        $manager->flush();
        return $title;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\CoreBundle\Entity\Term\Title');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->loadOneEntry($manager, "Monsieur");
        $this->loadOneEntry($manager, "Madame");
    }

}
