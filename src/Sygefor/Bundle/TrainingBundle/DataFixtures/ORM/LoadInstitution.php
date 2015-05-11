<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:15
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Institution;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadInstitution extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $populator->addEntity('Sygefor\Bundle\TrainingBundle\Entity\Term\Institution', 50, array(
            'organization' => function() use ($manager) {
                    $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
                    $size = count($organizations);
                    return $organizations[rand(0, $size - 1)];
                },
            'name' => function() use ($faker) { return $faker->city; },
            'city' => function() use ($faker) { return $faker->city; },
            'postal' => function() use ($faker) { return $faker->postcode; },
            'address' => function() use ($faker) { return $faker->address; },
            'position' => 0,
        ));
        $populator->execute();
    }
}
