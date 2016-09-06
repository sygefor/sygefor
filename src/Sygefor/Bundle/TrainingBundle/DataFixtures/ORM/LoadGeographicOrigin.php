<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:47
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadGeographicOrigin extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TrainingBundle\Entity\Term\Theme');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $this->loadOneEntry($manager, "Hors zone", null, 1);
        $this->loadOneEntry($manager, "Etablissement de rattachement", "default");
        $this->loadOneEntry($manager, "Agglomération");
        $this->loadOneEntry($manager, "Zone de compétence");

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param $name
     * @param $machineName
     * @return GeographicOrigin
     */
    public function loadOneEntry(ObjectManager $manager, $name, $machineName = null, $position = 0)
    {
        $origin = new GeographicOrigin();
        $origin->setId(++$this->autoId);
        $origin->setName($name);
        if ($machineName) {
            $origin->setMachineName($machineName);
        }
        $origin->setPosition($position);
        $manager->persist($origin);

        return $origin;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 1;
    }
}
