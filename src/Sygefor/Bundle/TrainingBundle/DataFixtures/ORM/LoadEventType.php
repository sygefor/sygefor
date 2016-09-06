<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\EventType;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEventType extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TrainingBundle\Entity\Term\EventType');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        foreach(array(
            "Journée d'étude",
            "Séminaire de recherche",
            "Colloque",
            "Autre"
        ) as $name) {
            $eventType = new EventType();
            $eventType->setId(++$this->autoId);
            $eventType->setName($name);
            if ($name === "Autre") {
                $eventType->setMachineName('other');
            }
            $eventType->setPosition($name == "Autre" ? 1 : 0);
            $manager->persist($eventType) ;
        }
        $manager->flush();
    }
}
