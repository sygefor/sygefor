<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 16/07/14
 * Time: 10:19
 */
namespace Sygefor\Bundle\TrainerBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;

class LoadTrainer extends AbstractTestDataFixture
{
    /**
     * Performs the actual fixtures loading.
     *
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     *
     * @param ObjectManager $manager The object manager.
     */
    protected function doLoad(ObjectManager $manager)
    {
/*        $faker = \Faker\Factory::create('fr_FR');
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
            'password' => function () use ($faker) { return $faker->sentence($nbWords = 1);},
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
            'username' => function() use ($faker) { return $faker->userName; },
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
*/
    }

} 