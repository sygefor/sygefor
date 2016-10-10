<?php

/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 15/04/14
 * Time: 10:03.
 */
namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\ORM\Doctrine\Populator;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractTestDataFixture;
use Sygefor\Bundle\MyCompanyBundle\Entity\Participation;
use Sygefor\Bundle\MyCompanyBundle\Entity\Trainer;
use Sygefor\Bundle\MyCompanyBundle\Entity\Session;

class LoadSessionCollections extends AbstractTestDataFixture
{
    protected function doLoad(ObjectManager $manager)
    {
        $faker     = \Faker\Factory::create('fr_FR');
        $populator = new Populator($faker, $manager);

        $sessions = $manager->getRepository('Sygefor\Bundle\TrainingBundle\Entity\Session')->findAll();
        foreach ($sessions as $session) {
            $populator->addEntity(Participation::class, 1, array(
                'session' => $session,
                'trainer' => function () use ($manager, $session) {
                    $trainers = $manager->getRepository(Trainer::class)->findBy(array('organization' => $session->getTraining()->getOrganization()));
                    $size = count($trainers);
                    if ($size === 0) {
                        return;
                    }

                    return $trainers[rand(0, $size - 1)];
                },
                'organization'    => $session->getTraining()->getOrganization(),
                'isOrganization'  => true,
            ));
            $populator->execute();
        }
    }

    public function getOrder()
    {
        return 6;
    }
}
