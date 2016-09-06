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
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadVariousAction extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->autoId = 0;
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $orgs = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();

        foreach ($orgs as $org) {
            foreach(array('Formation inter-Urfist', 'Formation dans les laboratoires', 'Atelier', 'Autre action diverse') as $name) {
                $variousAction = new VariousAction();
                $variousAction->setId(++$this->autoId);
                $variousAction->setName($name);
                if ($name === "Autre action diverse") {
                    $variousAction->setMachineName('other');
                }
                $variousAction->setOrganization($org);
                $manager->persist($variousAction) ;
            }
        }

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
