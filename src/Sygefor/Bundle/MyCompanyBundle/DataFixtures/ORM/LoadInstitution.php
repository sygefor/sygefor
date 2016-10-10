<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:15.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\MyCompanyBundle\Entity\Correspondent;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\GeographicOrigin;
use Sygefor\Bundle\InstitutionBundle\Entity\Term\InstitutionType;

class LoadInstitution extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $faker     = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker, $manager);
        $populator->addEntity('Sygefor\Bundle\MyCompanyBundle\Entity\Institution', 1, array(
            'organization' => function () use ($manager) {
                $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
                $size = count($organizations);

                return $organizations[rand(0, $size - 1)];
            },
            'name' => function () use ($faker) { return $faker->city; },
            'address' => function () use ($faker) { return $faker->address; },
            'zip' => function () use ($faker) { return $faker->postcode; },
            'city' => function () use ($faker) { return $faker->city; },
            'type' => function () use ($manager) {
                $types = $manager->getRepository(InstitutionType::class)->findAll();
                $size = count($types);
                if ($size > 0) {
                    return $types[rand(0, $size - 1)];
                }
                return null;
            },
            'geographicOrigin' => function () use ($manager) {
                $origins = $manager->getRepository(GeographicOrigin::class)->findAll();
                $size = count($origins);

                return $origins[rand(0, $size - 1)];
            }
        ));
        $populator->execute();
    }

    public function getCorrespondent($manager, $faker)
    {
        $correspondent = new Correspondent();
        // phoneNumber email address
        $correspondent->setTitle(call_user_func(function () use ($manager) {
            $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\PersonTrait\Term\Title')->findAll();
            $size = count($organizations);
            if ($size === 0)
                return;

            return $organizations[rand(0, $size - 1)];
        }));
        $correspondent->setFirstName(call_user_func(function () use ($faker) { return $faker->firstName();}));
        $correspondent->setLastName(call_user_func(function () use ($faker) { return $faker->lastName();}));
        $correspondent->setPhoneNumber(call_user_func(function () use ($faker) { return $faker->phoneNumber();}));
        $correspondent->setEmail(call_user_func(function () use ($faker) { return $faker->email();}));

        return $correspondent;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    function getOrder()
    {
        return 2;
    }
}
