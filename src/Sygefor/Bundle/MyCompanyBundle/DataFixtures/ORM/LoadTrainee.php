<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 20/05/14
 * Time: 18:08.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\MyCompanyBundle\Entity\Institution;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\InstitutionBundle\Entity\AbstractInstitution;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;

/**
 * Class LoadTrainee.
 */
class LoadTrainee extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker     = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker, $manager);

        $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
        foreach ($organizations as $organization) {
            $populator->addEntity('Sygefor\Bundle\MyCompanyBundle\Entity\Trainee', $faker->numberBetween(1, 1), array(
                'organization' => $organization,
                'title'        => function () use ($manager) {
                    $titles = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term\Title')->findAll();
                    $size = count($titles);
                    if ($size === 0)
                        return;

                    return $titles[rand(0, $size - 1)];
                },
                'firstName' => function () use ($faker) {
                    return $faker->firstName();
                },
                'lastName' => function () use ($faker) {
                    return $faker->lastName();
                },

                'address' => function () use ($faker) {
                    return $faker->streetAddress;
                },
                'zip' => function () use ($faker) {
                    return $faker->postcode;
                },
                'city' => function () use ($faker) {
                    return $faker->city;
                },
                'email' => function () use ($faker) {
                    return $faker->email();
                },
                'phoneNumber' => function () use ($faker) {
                    return $faker->phoneNumber();
                },
                'website' => null,

                'institution' => function () use ($manager, $organization) {
                    $institutions = $manager->getRepository(Institution::class)->findBy(array('organization' => $organization));
                    $size = count($institutions);
                    if ($size === 0)
                        return;

                    return $institutions[rand(0, $size - 1)];
                },
                'service' => function () use ($faker) {
                    return $faker->sentence($nbWords = 3);
                },
                'publicType' => function () use ($manager) {
                    $types = $manager->getRepository(PublicType::class)->findAll();
                    $size = count($types);
                    if ($size === 0)
                        return;

                    return $types[rand(0, $size - 1)];
                },
            ));
            $populator->execute();
        }

    }

    function getOrder()
    {
        return 4;
    }
}
