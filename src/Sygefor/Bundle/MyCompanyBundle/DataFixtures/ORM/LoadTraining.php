<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:03.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\MyCompanyBundle\Entity\Institution;
use Sygefor\Bundle\TrainingBundle\Entity\Training\AbstractTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Tag;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Supervisor;
use Sygefor\Bundle\TrainingBundle\Entity\Training\Term\TrainingCategory;

abstract class LoadTraining extends AbstractTestDataFixture
{
    static $class = AbstractTraining::class;

    /**
     * @param ObjectManager $manager
     *
     * @return \Faker\Generator
     */
    public function doLoad(ObjectManager $manager)
    {
        $faker     = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker, $manager);

        $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
        foreach ($organizations as $organization) {
            $populator->addEntity($this::$class, $faker->randomNumber(1, 1), array_merge(
                array(
                    // base training
                    'organization' => $organization,
                    'name'         => function () use ($faker) {
                        return $faker->sentence($nbWords = 6);
                    },
                    'theme' => function () use ($manager) {
                        $themes = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Training\Term\Theme')->findAll();
                        $size = count($themes);

                        return $themes[rand(0, $size - 1)];
                    },
                    'program' => function () use ($faker) {
                        return $faker->sentence($nbWords = 50);
                    },
                    'description' => function () use ($faker) {
                        return $faker->sentence($nbWords = 50);
                    },
                    'teachingMethods' => function () use ($faker) {
                        return $faker->sentence($nbWords = 30);
                    },
                    'institution' => function () use ($manager, $organization) {
                        $institutions = $manager->getRepository(Institution::class)->findBy(array('organization' => $organization));
                        $size = count($institutions);

                        return $institutions[rand(0, $size - 1)];
                    },
                    'category' => function () use ($manager) {
                        $categories = $manager->getRepository(TrainingCategory::class)->findBy(array('trainingType' => array(null, 'internship')));
                        $size = count($categories);
                        if (!$size) {
                            return null;
                        }

                        return $categories[rand(0, $size - 1)];
                    },
                    'tags' => function () use ($manager, $faker, $organization) {
                        $areas = $manager->getRepository(Tag::class)->findBy(array('organization' => $organization));
                        $size = count($areas);
                        $areaCollection = new ArrayCollection();
                        $i = 0;
                        while ($i < $faker->randomNumber(0, 2)) {
                            $area = $areas[rand(0, $size - 1)];
                            if ( ! $areaCollection->contains($area)) {
                                $areaCollection->add($area);
                                ++$i;
                            }
                        }
                    },
                    'supervisor' => function () use ($manager, $organization) {
                        $supervisors = $manager->getRepository(Supervisor::class)->findBy(array('organization' => $organization));
                        $size = count($supervisors);

                        if ($size > 0) {
                            return $supervisors[rand(0, $size - 1)];
                        }
                        return null;
                    },
                    'interventionType' => function () use ($faker) {
                        return $faker->boolean($chanceOfGettingTrue = 50) ? 'Travaux pratiques' : '';
                    },
                    'externalInitiative' => function () use ($faker) {
                        return $faker->boolean($chanceOfGettingTrue = 15);
                    },
                    'comments' => function () use ($faker) {
                        return $faker->sentence($nbWords = 10);
                    },
                    'firstSessionPeriodSemester' => function () use ($faker) {
                        return $faker->randomNumber(1, 1);
                    },
                    'firstSessionPeriodYear' => function () use ($faker) {
                        return $faker->randomNumber(2016, 2016);
                    },
                ),
                $this->getOtherFields($manager, $faker, $organization)
            ));
            $populator->execute();
        }
    }

    abstract protected function getOtherFields(ObjectManager $manager, $faker, Organization $organization);

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    function getOrder()
    {
        return 4;
    }
}
