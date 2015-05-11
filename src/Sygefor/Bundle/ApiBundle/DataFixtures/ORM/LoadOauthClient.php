<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 08/04/14
 * Time: 15:53
 */

namespace Sygefor\Bundle\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\ApiBundle\Entity\Client;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\TrainingBundle\Entity\Term\Theme;
use Sygefor\Bundle\TrainingBundle\Entity\Term\VariousAction;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadOauthClient
 * @package Sygefor\Bundle\TrainingBundle\DataFixtures\ORM
 */
class LoadOauthClient extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\TrainingBundle\Entity\Term\Theme');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $client = new Client();
        $client->setId(1);
        $client->setRandomId("5cn6gjovjzks48ckkowkgko8owk8o8ccwow8o4w0c84c40kwsk");
        $client->setRedirectUris(array(
            'http://localhost:3000',
            'http://sygefor.reseau-urfist.fr',
        ));
        $client->setAllowedGrantTypes(array(
            'password',
            'refresh_token',
          ));
        $client->setPublic(true);

        $manager->persist($client) ;
        $manager->flush();
    }
}
