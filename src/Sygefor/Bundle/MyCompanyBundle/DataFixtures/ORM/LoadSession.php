<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 15:39.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Session\Term\Place;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;

class LoadSession extends AbstractTestDataFixture
{
    /** @var AbstractTraining */
    protected $training;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $faker     = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker, $manager);
        $trainings = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining')->findAll();
        foreach ($trainings as $training) {
            $populator->addEntity('Sygefor\Bundle\MyCompanyBundle\Entity\Session', $faker->numberBetween(1, 1), array(
                'training' => $training,
                'name'   => function () use ($faker) {
                    return $faker->sentence($nbWords = 6);
                },
                'promote' => function () use ($faker) {
                    return $faker->boolean($chanceOfGettingTrue = 10);
                },
                'registration' => function () use ($faker) {
                    return $faker->numberBetween(0, 3);
                },
                'displayOnline' => function () use ($faker) {
                    return $faker->boolean($chanceOfGettingTrue = 80);
                },

                'dateBegin' => function () use ($faker) {
                    return $faker->dateTimeThisYear();
                },
                'dateEnd'            => null,
                'hourNumber'         => $faker->numberBetween(4, 7),
                'dayNumber'          => $faker->numberBetween(1, 1),

                'place' => function () use ($training, $manager) {
                    $places = $manager->getRepository(Place::class)->findBy(array('organization' => $training->getOrganization()));
                    $size = count($places);
                    if ($size === 0)
                        return;

                    return $places[rand(0, $size - 1)];
                },
                'status' => function () use ($faker) {
                    return $faker->numberBetween(0, 2);
                },
                'numberOfRegistrations' => function () use ($faker) {
                    return $faker->numberBetween(15, 30);
                },
                'maximumNumberOfRegistrations' => function () use ($faker) {
                    return $faker->numberBetween(15, 30);
                },
                'limitRegistrationDate' => null,
                'participantsSummaries' => null,
            ));
            $populator->execute();
        }
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    function getOrder()
    {
        return 5;
    }
}
