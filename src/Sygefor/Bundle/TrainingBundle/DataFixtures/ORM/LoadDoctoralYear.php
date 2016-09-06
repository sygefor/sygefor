<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 25/02/2015
 * Time: 16:35
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralYear;
use Sygefor\Bundle\TrainingBundle\Entity\Term\EventKind;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadDoctoralYear extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TrainingBundle\Entity\Term\DoctoralYear');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        foreach(array('1ère Année', '2ème Année', '3ème Année') as $name) {
            $doctoralYear = new DoctoralYear();
            $doctoralYear->setId(++$this->autoId);
            $doctoralYear->setName($name);
            $manager->persist($doctoralYear);
        }
        $manager->flush();
    }
}