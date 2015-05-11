<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 03/06/14
 * Time: 17:59
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
class LoadInscription extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager) {
        $faker = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker , $manager);
        $sessions = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Session')->findAll();
        $trainees = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Trainee')->findAll();
        $inscriptionStatus = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus')->findAll();
        $presenceStatus = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus')->findAll();

        $populator->addEntity('Sygefor\Bundle\TraineeBundle\Entity\Inscription', 200, array(
            'trainee' => function() use ($trainees) {
                    $size = count($trainees);
                    if ($size == 0)
                        return null;
                    return $trainees[rand(0, $size - 1)];
                },
            'session' => function() use ($sessions) {
                    $size = count($sessions);
                    if ($size == 0)
                        return null;
                    return $sessions[rand(0, $size - 1)];
                },
            'inscriptionStatus' => function() use ($inscriptionStatus) {
                    $size = count($inscriptionStatus);
                    if ($size == 0)
                        return null;
                    return $inscriptionStatus[rand(0, $size - 1)];
                },
            'presenceStatus' => function() use ($presenceStatus) {
                    $size = count($presenceStatus);
                    if ($size == 0)
                        return null;
                    return $presenceStatus[rand(0, $size - 1)];
                },
        ));
        $populator->execute();
    }

    /**
     * {@inheritdoc}
     */
    function getOrder() {
        return 4;
    }
}
