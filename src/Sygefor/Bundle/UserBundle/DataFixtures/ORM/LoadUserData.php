<?php
namespace Sygefor\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractDataFixture
{
    private $localAdminAccessRights = array(
      "sygefor_user.rights.user.own",
      "sygefor_taxonomy.rights.vocabulary.own",
      "sygefor_training.rights.training.own.view",
      "sygefor_training.rights.training.own.create",
      "sygefor_training.rights.training.own.update",
      "sygefor_training.rights.training.own.delete",
      "sygefor_trainee.rights.trainee.own.view",
      "sygefor_trainee.rights.trainee.own.create",
      "sygefor_trainee.rights.trainee.own.update",
      "sygefor_trainee.rights.trainee.own.delete",
      "sygefor_trainee.rights.inscription.own.view",
      "sygefor_trainee.rights.inscription.own.create",
      "sygefor_trainee.rights.inscription.own.update",
      "sygefor_trainee.rights.inscription.own.delete",
      "sygefor_trainer.rights.trainer.own.view",
      "sygefor_trainer.rights.trainer.own.create",
      "sygefor_trainer.rights.trainer.own.update",
      "sygefor_trainer.rights.trainer.own.delete"
    );

    private $nationalAdminAccessRights = array(
      "sygefor_user.rights.user.all",
      "sygefor_user.rights.group",
      "sygefor_taxonomy.rights.vocabulary.all",
      "sygefor_taxonomy.rights.vocabulary.national",
      "sygefor_taxonomy.rights.vocabulary.own",
      "sygefor_training.rights.training.all.view",
      "sygefor_training.rights.training.all.create",
      "sygefor_training.rights.training.all.update",
      "sygefor_training.rights.training.all.delete",
      "sygefor_trainee.rights.trainee.all.view",
      "sygefor_trainee.rights.trainee.all.create",
      "sygefor_trainee.rights.trainee.all.update",
      "sygefor_trainee.rights.trainee.all.delete",
      "sygefor_trainee.rights.inscription.all.view",
      "sygefor_trainee.rights.inscription.all.create",
      "sygefor_trainee.rights.inscription.all.update",
      "sygefor_trainee.rights.inscription.all.delete",
      "sygefor_trainer.rights.trainer.all.view",
      "sygefor_trainer.rights.trainer.all.create",
      "sygefor_trainer.rights.trainer.all.update",
      "sygefor_trainer.rights.trainer.all.delete"
    );

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $encoderFactory = $this->container->get('security.encoder_factory');
        $organizations = $manager->getRepository('SygeforCoreBundle:Organization')->findAll();

        /** @var Organization $organization */
        foreach($organizations as $organization) {
            $user = new User();
            $user->setOrganization($organization);
            $user->setUsername($organization->getCode());
            $user->setAccessRights($this->localAdminAccessRights);

            $encoder = $encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($organization->getCode() . substr($organization->getZip(), 0, 2),$user->getSalt()));
            $user->setEmail($organization->getEmail()) ;
            $user->setEnabled(true);
            $manager->persist($user) ;
        }

        // admin
        $user = new User();
        $user->setUsername("admin");
        $encoder = $encoderFactory->getEncoder($user);
        $user->setPassword($encoder->encodePassword("adm1n", $user->getSalt()));
        $user->setEmail("admin@localhost") ;
        $user->setEnabled(1);
        $user->setRoles(array ('ROLE_ADMIN')) ;
        $user->setOrganization($manager->getRepository('SygeforCoreBundle:Organization')->find(1));
        $manager->persist($user) ;

        // flush
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 2;
    }

}
