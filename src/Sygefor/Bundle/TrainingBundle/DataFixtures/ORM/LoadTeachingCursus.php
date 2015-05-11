<?php
namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTeachingCursus
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadTeachingCursus extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $name
     * @return TeachingCursus
     */
    public function loadOneEntry(ObjectManager $manager, $name)
    {
        $teachingCursus = new TeachingCursus();
        $teachingCursus->setId(++$this->autoId);
        $teachingCursus->setName($name);
        $manager->persist($teachingCursus) ;
        $manager->flush();
        return $teachingCursus;
    }

    /**
     * @param ObjectManager $manager
     * @param $parent
     * @param $array
     */
    public function loadChildEntries(ObjectManager $manager, $parent, $array)
    {
        foreach($array as $name) {
            $teachingCursus = new TeachingCursus();
            $teachingCursus->setId(++$this->autoId);
            $teachingCursus->setName($name);
            $teachingCursus->setParent($parent);
            $manager->persist($teachingCursus) ;
        }
        $manager->flush();
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->loadOneEntry($manager, 'Licence');
        $this->loadOneEntry($manager, 'Formation d\'ingénieur');
        $master = $this->loadOneEntry($manager, 'Master');

        $this->loadChildEntries($manager, $master, array(
            'Master 1ère année',
            'Master 2ème année'
        ));

        $doctorat = $this->loadOneEntry($manager, "Doctorat");
        $this->loadChildEntries($manager, $doctorat, array(
            'Doctorat 1ère année',
            'Doctorat 2ème année',
            'Doctorat 3ème année'
        ));

        $this->loadOneEntry($manager, "Autre");
    }
}
