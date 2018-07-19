<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Organization;
use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\User;

class LoadUserData extends AbstractDataFixture
{
    protected $accessRights = array(
        'sygefor_core.access_right.vocabulary.own',
        'sygefor_core.access_right.vocabulary.all',
        'sygefor_core.access_right.vocabulary.national',
        'sygefor_core.access_right.vocabulary.view.all',
        'sygefor_core.access_right.user.own',
        'sygefor_core.access_right.user.all',
        'sygefor_core.access_right.inscription.own.view',
        'sygefor_core.access_right.inscription.own.create',
        'sygefor_core.access_right.inscription.own.update',
        'sygefor_core.access_right.inscription.own.delete',
        'sygefor_core.access_right.inscription.all.view',
        'sygefor_core.access_right.inscription.all.create',
        'sygefor_core.access_right.inscription.all.update',
        'sygefor_core.access_right.inscription.all.delete',
        'sygefor_core.access_right.trainee.own.view',
        'sygefor_core.access_right.trainee.own.create',
        'sygefor_core.access_right.trainee.own.update',
        'sygefor_core.access_right.trainee.own.delete',
        'sygefor_core.access_right.trainee.all.view',
        'sygefor_core.access_right.trainee.all.create',
        'sygefor_core.access_right.trainee.all.update',
        'sygefor_core.access_right.trainee.all.delete',
        'sygefor_core.access_right.trainer.own.view',
        'sygefor_core.access_right.trainer.own.create',
        'sygefor_core.access_right.trainer.own.update',
        'sygefor_core.access_right.trainer.own.delete',
        'sygefor_core.access_right.trainer.all.view',
        'sygefor_core.access_right.trainer.all.create',
        'sygefor_core.access_right.trainer.all.update',
        'sygefor_core.access_right.trainer.all.delete',
        'sygefor_core.access_right.training.own.view',
        'sygefor_core.access_right.training.own.create',
        'sygefor_core.access_right.training.own.update',
        'sygefor_core.access_right.training.own.delete',
        'sygefor_core.access_right.training.all.view',
        'sygefor_core.access_right.training.all.create',
        'sygefor_core.access_right.training.all.update',
        'sygefor_core.access_right.training.all.delete',
        'sygefor_activity_report.rights.balance',
    );

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $encoderFactory = $this->container->get('security.encoder_factory');
        $organizations = $manager->getRepository(Organization::class)->findAll();

        // admin
        $user = new User();
        $user->setUsername('admin');
        $encoder = $encoderFactory->getEncoder($user);
        $user->setPassword($encoder->encodePassword('admin', $user->getSalt()));
        $user->setEmail('admin@sygefor.com');
        $user->setEnabled(1);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setAccessRights($this->accessRights);
        $user->setOrganization($manager->getRepository(Organization::class)->find(1));
        $manager->persist($user);

        /* @var Organization $organization */
        foreach ($organizations as $organization) {
            $user = new User();
            $user->setOrganization($organization);
            $user->setUsername($organization->getCode());
            $user->setAccessRights($this->getLocalAccessRights());

            $encoder = $encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($organization->getCode(), $user->getSalt()));
            $user->setEmail($organization->getEmail());
            $user->setEnabled(true);
            $manager->persist($user);
        }

        // flush
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * @return array
     */
    protected function getLocalAccessRights()
    {
        $userAccessRights = array();
        foreach ($this->accessRights as $accessRight) {
            if (strstr($accessRight, 'own')) {
                $userAccessRights[] = $accessRight;
            }
        }
        $userAccessRights[] = 'sygefor_core.access_right.vocabulary.view.all';
        $userAccessRights[] = 'sygefor_activity_report.rights.balance';

        return $userAccessRights;
    }
}
