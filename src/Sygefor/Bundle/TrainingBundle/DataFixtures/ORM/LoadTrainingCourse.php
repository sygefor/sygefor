<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:14
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus;
use Sygefor\Bundle\TrainingBundle\Entity\Term\TrainingCourse;
use Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary;
use Sygefor\Bundle\TrainingBundle\Entity\Internship;
use Sygefor\Bundle\TrainingBundle\Entity\Term\EventType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTrainingCourse
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadTrainingCourse extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $populator->addEntity('Sygefor\Bundle\TrainingBundle\Entity\TrainingCourse', 50, array(
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
            'supervisor' => function() use ($faker) { return $faker->name; },
            'institution' => function() use ($manager) {
                    $institutions = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Term\Institution')->findAll();
                    $size = count($institutions);
                    return $institutions[rand(0, $size - 1)];
                },
            'disciplinary' => function() use ($manager) {
                    $disciplinaries = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Term\Disciplinary')->findAll();
                    $size = count($disciplinaries);
                    return $disciplinaries[rand(0, $size - 1)];
                },
            'teachingCursus' => function() use ($manager) {
                    $teachingCursus = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus')->findAll();
                    $size = count($teachingCursus);
                    return $teachingCursus[rand(0, $size - 1)];
                },
            'otherCursus' => function() use ($faker) { return $faker->sentence($nbWords = 2); },
            'evaluation' => function() use ($faker) { return $faker->boolean($chanceOfGettingTrue = 50); },
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
