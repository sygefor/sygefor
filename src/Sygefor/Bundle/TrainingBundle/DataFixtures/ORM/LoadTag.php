<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 09:30
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Tag;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTag extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $populator->addEntity('Sygefor\Bundle\TrainingBundle\Entity\Term\Tag', 50, array(
            'name' => function() use ($faker) { return $faker->sentence($nbWords = 1); },
            'organization' => function() use ($manager) {
                $organizations = $manager->getRepository('Sygefor\Bundle\CoreBundle\Entity\Organization')->findAll();
                $size = count($organizations);
                return $organizations[rand(0, $size - 1)];
            },
        ));
        $populator->execute();
    }
}
