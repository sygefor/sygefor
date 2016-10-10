<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:03.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TraineeBundle\Entity\Term\PublicType;
use Sygefor\Bundle\MyCompanyBundle\Entity\Internship;

class LoadInternship extends LoadTraining
{
    static $class = Internship::class;

    protected function getOtherFields(ObjectManager $manager, $faker, Organization $organization)
    {
        return array(
            'prerequisites' => call_user_func(function () use ($faker) { return $faker->sentence($nbWords = 20);}),
            'publicType' => call_user_func(function () use ($manager) {
                $publicTypes = $manager->getRepository(PublicType::class)->findAll();
                $size = count($publicTypes);

                return $publicTypes[rand(0, $size - 1)];
            }),
        );
    }
}
