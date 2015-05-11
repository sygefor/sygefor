<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 15:39
 */

namespace Sygefor\Bundle\TrainingBundle\DataFixtures\ORM;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class LoadSession extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $populator->addEntity('Sygefor\Bundle\TrainingBundle\Entity\Session', 500, array(
            'training' => function() use ($manager) {

                    // Get connection
                    $conn = $manager->getConnection();

                    // Get table name
                    $meta = $manager->getClassMetadata('Sygefor\Bundle\TrainingBundle\Entity\Training');
                    $tableName = $meta->getTableName();

                    // Get random ids
                    $sql = "SELECT id AS id FROM $tableName ORDER BY RAND() LIMIT 0,1";
                    //avoiding SingleSession
                    do {
                        $statement = $conn->executeQuery($sql);
                        $first = $statement->fetch() ;
                        $fetchedId = $first['id'] ;


                        $training = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Training')->findBy(array('id'=>$fetchedId));
                        $training = $training[0];
                    }while(method_exists($training,'setSession'));

                    return $training;


                },
            'dateBegin' => function() use ($faker) { return $faker->dateTimeBetween('-5 years', '5 years'); },
            'dateEnd' => function() use ($faker) { return $faker->dateTimeBetween('-5 years', '5 years'); },
            'limitRegistrationDate' => function() use ($faker) { return $faker->dateTimeBetween('-5 years', '5 years'); },
            'hourDuration' => function() use ($faker) { return $faker->numberBetween(30, 180); },
            'price' => function() use ($faker) { return $faker->numberBetween(0, 500); },
            'maximumNumberOfRegistrations' => function() use ($faker) { return $faker->numberBetween(90, 100); },
            'numberOfRegistrations' => function() use ($faker) { return $faker->numberBetween(70, 90); },
            'numberOfParticipants' => function() use ($faker) { return $faker->numberBetween(50, 70); },
            'place' => function() use ($faker) { return $faker->address; },
            'registration' => function() use ($faker) { return $faker->boolean($chanceOfGettingTrue = 50); },
            'networkTrainerCost' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'externTrainerCost' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'externTrainerConsideration' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'reprographyCost' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'otherCost' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'subscriptionRightTaking' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'otherTaking' => function() use ($faker) { return $faker->numberBetween(100, 5000); },
            'observations' => function() use ($faker) { return $faker->sentence($nbWords = 15); },
        ));

        $populator->execute();

    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder() {
        return 3;
    }
}
