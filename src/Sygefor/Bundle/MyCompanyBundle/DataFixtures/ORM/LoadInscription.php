<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 03/06/14
 * Time: 17:59.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\TraineeBundle\Entity\Term\CompetitionStatus;
use Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee;

/**
 * Class LoadInscription.
 */
class LoadInscription extends AbstractTestDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $faker             = \Faker\Factory::create('fr_FR');
        $populator         = new Populator($faker, $manager);
        $inscriptionStatus = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\InscriptionStatus')->findAll();
        $presenceStatus    = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Term\PresenceStatus')->findAll();
        $competitionStatus = $manager->getRepository(CompetitionStatus::class)->findAll();

        $sessions = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Session')->findAll();
        foreach ($sessions as $session) {
            $organization = $session->getTraining()->getOrganization();
            $trainees     = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\AbstractTrainee')->findBy(array('organization' => $organization));

            $populator->addEntity('Sygefor\Bundle\TraineeBundle\Entity\Inscription', 1, array(
                'trainee' => function () use ($trainees) {
                    $size = count($trainees);
                    if ($size === 0) {
                        return;
                    }

                    return $trainees[rand(0, $size - 1)];
                },
                'session' => function () use ($sessions) {
                    $size = count($sessions);
                    if ($size === 0)
                        return;

                    return $sessions[rand(0, $size - 1)];
                },
                'inscriptionStatus' => function () use ($inscriptionStatus) {
                    $size = count($inscriptionStatus);
                    if ($size === 0)
                        return;

                    return $inscriptionStatus[rand(0, $size - 1)];
                },
                'presenceStatus' => function () use ($presenceStatus) {
                    $size = count($presenceStatus);
                    if ($size === 0)
                        return;

                    return $presenceStatus[rand(0, $size - 1)];
                },
                'competitionStatus' => function () use ($competitionStatus) {
                    $size = count($competitionStatus);
                    if ($size === 0)
                        return;

                    return $competitionStatus[rand(0, $size - 1)];
                },
                'presenceHours' => function () use ($faker) { return $faker->numberBetween(4, 7); },
            ));
            $populator->execute();
        }

        $inscriptions = $manager->getRepository('Sygefor\Bundle\TraineeBundle\Entity\Inscription')->findAll();
        foreach ($inscriptions as $inscription) {
            $inscription->getSession()->addInscription($inscription);
        }
    }

    /**
     * {@inheritdoc}
     */
    function getOrder()
    {
        return 6;
    }
}
