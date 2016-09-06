<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:03
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\EventType;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Internship;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadInternship extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $populator->addEntity('Sygefor\Bundle\TrainingBundle\Entity\Internship', 50, array(
            'organization' => function() use ($manager) {
                    $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
                    $size = count($organizations);
                    return $organizations[rand(0, $size - 1)];
                },
            'theme' => function() use ($manager) {
                    $themes = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Term\Theme')->findAll();
                    $size = count($themes);
                    return $themes[rand(0, $size - 1)];
                },
            'name' => function() use ($faker) { return $faker->sentence($nbWords = 6); },
            'externInitiative' => function() use ($faker) { return $faker->boolean($chanceOfGettingTrue = 50); },
//            'supervisor' => function() use ($faker) { return $faker->name; },
            'firstSessionPeriodSemester' => function() use ($faker) { return $faker->randomNumber(1, 2); },
            'firstSessionPeriodYear' => function() use ($faker) { return $faker->randomNumber(1990, 2020); },

        ));
        $populator->execute();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder() {
        return 2;
    }
}
