<?php

namespace Sygefor\Bundle\MyCompanyBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Entity\User\User;

class LoadUserData extends AbstractDataFixture
{
    private $localAdminAccessRights = array(
        'sygefor_core.rights.user.own',
        'sygefor_core.rights.vocabulary.own',
        'sygefor_inscription.rights.inscription.own.create',
        'sygefor_inscription.rights.inscription.own.delete',
        'sygefor_inscription.rights.inscription.own.update',
        'sygefor_inscription.rights.inscription.own.view',
        'sygefor_institution.rights.institution.own.create',
        'sygefor_institution.rights.institution.own.delete',
        'sygefor_institution.rights.institution.own.update',
        'sygefor_institution.rights.institution.own.view',
        'sygefor_trainee.rights.trainee.own.create',
        'sygefor_trainee.rights.trainee.own.delete',
        'sygefor_trainee.rights.trainee.own.update',
        'sygefor_trainee.rights.trainee.own.view',
        'sygefor_trainer.rights.trainer.own.create',
        'sygefor_trainer.rights.trainer.own.delete',
        'sygefor_trainer.rights.trainer.own.update',
        'sygefor_trainer.rights.trainer.own.view',
        'sygefor_training.rights.training.own.create',
        'sygefor_training.rights.training.own.delete',
        'sygefor_training.rights.training.own.update',
        'sygefor_training.rights.training.own.view',
    );

    private $nationalAdminAccessRights = array(
        'sygefor_core.rights.user.all',
        'sygefor_core.rights.vocabulary.all',
        'sygefor_core.rights.vocabulary.national',
        'sygefor_inscription.rights.inscription.all.create',
        'sygefor_inscription.rights.inscription.all.delete',
        'sygefor_inscription.rights.inscription.all.update',
        'sygefor_inscription.rights.inscription.all.view',
        'sygefor_institution.rights.institution.all.create',
        'sygefor_institution.rights.institution.all.delete',
        'sygefor_institution.rights.institution.all.update',
        'sygefor_institution.rights.institution.all.view',
        'sygefor_trainee.rights.trainee.all.create',
        'sygefor_trainee.rights.trainee.all.delete',
        'sygefor_trainee.rights.trainee.all.update',
        'sygefor_trainee.rights.trainee.all.view',
        'sygefor_trainer.rights.trainer.all.create',
        'sygefor_trainer.rights.trainer.all.delete',
        'sygefor_trainer.rights.trainer.all.update',
        'sygefor_trainer.rights.trainer.all.view',
        'sygefor_training.rights.training.all.create',
        'sygefor_training.rights.training.all.delete',
        'sygefor_training.rights.training.all.update',
        'sygefor_training.rights.training.all.view'
    );

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $encoderFactory = $this->container->get('security.encoder_factory');
        $organizations = $manager->getRepository('SygeforCoreBundle:Organization')->findAll();

        // admin
        $user = new User();
        $user->setUsername('admin');
        $encoder = $encoderFactory->getEncoder($user);
        $user->setPassword($encoder->encodePassword('admin', $user->getSalt()));
        $user->setEmail('admin@sygefor.dev');
        $user->setEnabled(1);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setAccessRights($this->nationalAdminAccessRights + $this->localAdminAccessRights);
        $user->setOrganization($manager->getRepository('SygeforCoreBundle:Organization')->find(1));
        $manager->persist($user);

        /** @var Organization $organization */
        foreach ($organizations as $organization) {
            $user = new User();
            $user->setOrganization($organization);
            $user->setUsername($organization->getCode());
            $user->setAccessRights($this->localAdminAccessRights);

            $encoder = $encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($organization->getCode() . substr($organization->getZip(), 0, 2), $user->getSalt()));
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
    function getOrder()
    {
        return 2;
    }
}
