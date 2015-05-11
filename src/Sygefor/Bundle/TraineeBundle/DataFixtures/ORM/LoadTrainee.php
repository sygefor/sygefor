<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/05/14
 * Time: 18:08
 */

namespace Sygefor\Bundle\TraineeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadTrainee
 * @package Sygefor\Bundle\TraineeBundle\DataFixtures\ORM
 */
class LoadTrainee extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $populator->addEntity('Sygefor\Bundle\TraineeBundle\Entity\Trainee', 200, array(
            'organization' => function() use ($manager) {
                    $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
                    $size = count($organizations);
                    if ($size == 0)
                        return null;
                    return $organizations[rand(0, $size - 1)];
                },
            'institution' => function() use ($manager) {
                    $organizations = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Term\Institution')->findAll();
                    $size = count($organizations);
                    if ($size == 0)
                        return null;
                    return $organizations[rand(0, $size - 1)];
                },
            'disciplinary' => function() use ($manager) {
                    $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Term\Disciplinary')->findAll();
                    $size = count($organizations);
                    if ($size == 0)
                        return null;
                    return $organizations[rand(0, $size - 1)];
                },
            'title' => function() use ($manager) {
                    $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Term\Title')->findAll();
                    $size = count($organizations);
                    if ($size == 0)
                        return null;
                    return $organizations[rand(0, $size - 1)];
                },
            'service' => function () use ($faker) { return $faker->sentence($nbWords = 3);},
            'status' => function () use ($faker) { return $faker->sentence($nbWords = 7);},
            'firstName' => function () use ($faker) { return $faker->firstName();},
            'lastName' => function () use ($faker) { return $faker->lastName();},
            'email' => function () use ($faker) { return $faker->email();},
            'phoneNumber' => function () use ($faker) { return $faker->phoneNumber();},
            'address' => function() use ($faker) { return $faker->streetAddress; },
            'addressType' => function() use ($faker) { return $faker->boolean($chanceOfGettingTrue = 50); },
            'institutionName' => function() use ($faker) { return $faker->city; },
            'zip' => function() use ($faker) { return $faker->postcode; },
            'city' => function() use ($faker) { return $faker->city; },
            'bp' => '',
            'cedex' => '',
            'otherInstitution' => '',
            'publicType' => function() use ($manager) {
                $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Term\PublicType')->findAll();
                $size = count($organizations);
                    if ($size == 0)
                        return null;
                return $organizations[rand(0, $size - 1)];
            },
            'teachingCursus' => function() use ($manager) {
                $organizations = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Term\TeachingCursus')->findAll();
                $size = count($organizations);
                    if ($size == 0)
                        return null;
                return $organizations[rand(0, $size - 1)];
            },
        ));

        $populator->execute();

    }
}
