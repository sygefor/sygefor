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
use Sygefor\Bundle\TrainingBundle\Entity\Term\Theme;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTheme
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadTheme extends AbstractDataFixture
{
    /**
     * @param ObjectManager $manager
     * @param $name
     * @param $position
     * @return Theme
     */
    public function loadOneEntry(ObjectManager $manager, $name, $position = 0) {
        $theme = new Theme();
        $theme->setId(++$this->autoId);
        $theme->setName($name);
        if ($name === "Autre") {
            $theme->setMachineName('other');
        }
        $theme->setPosition($position);
        $manager->persist($theme) ;
        $manager->flush();
        return $theme;
    }

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

        $this->loadOneEntry($manager, "Recherche d'information");
        $this->loadOneEntry($manager, "Gestion de l'information");
        $this->loadOneEntry($manager, "Publication et droit de l'information");
        $this->loadOneEntry($manager, "Production et traitement de l'information");
        $this->loadOneEntry($manager, "Compétences informationnelles, ingénierie de formation");
        $this->loadOneEntry($manager, "Contexte, enjeux de l'IST");
        $this->loadOneEntry($manager, "Autre", 1);
    }
}
