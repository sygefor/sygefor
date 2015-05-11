<?php
namespace Sygefor\Bundle\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sygefor\Bundle\CoreBundle\DataFixtures\AbstractDataFixture;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class LoadOrganizationData
 * @package Sygefor\Bundle\UserBundle\DataFixtures\ORM
 *
 * php app/console doctrine:query:sql "SET foreign_key_checks = 0; DELETE FROM organization; DELETE FROM title;"
 * php app/console doctrine:fixtures:load --fixtures=src/Sygefor/Bundle/CoreBundle/DataFixtures/ORM --append
 * php app/console doctrine:query:sql "SET foreign_key_checks = 1;"
 *
 */
class LoadOrganizationData extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $metadata = $manager->getClassMetaData('Sygefor\Bundle\CoreBundle\Entity\Organization');
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $accessor = PropertyAccess::createPropertyAccessor();

        $data = array(
          'Rennes' => array(
            'departments' => array(22, 29, 35, 56, 44, 49, 53, 72, 85),
            'institutionName' => 'Université Rennes 2',
            'address' => 'Place du Recteur Henri Le Moal',
            'bp' => 'CS 64302',
            'zip' => '35043',
            'city' => 'RENNES CEDEX',
            'phoneNumber' => '02 99 14 14 46',
            'faxNumber' => '02 99 14 14 47',
            'email' => 'urfist@uhb.fr',
            'website' => 'http://www.sites.univ-rennes2.fr/urfist',
            'map' => array(
              'lat' => 48.12104,
              'lon' => -1.70146,
              'zoom' => 15
            )
          ),
          'Lyon' => array(
            'departments' => array("03", 15, 43, 63, 21, 58, 71, 89, "01", "07", 26, 38, 42, 69, 73, 74),
            'institutionName' => 'Campus de la Doua  -  BU Sciences',
            'address' => '20, Avenue Gaston Berger',
            'bp' => 'BP 72215',
            'zip' => '69603',
            'city' => 'VILLEURBANNE CEDEX',
            'phoneNumber' => '04 72 44 80 86',
            'faxNumber' => '04 26 23 45 28',
            'email' => 'sophie.ropert@univ-lyon1.fr',
            'website' => 'http://urfist.univ-lyon1.fr'
          ),
          'Nice' => array(
            'departments' => array("04", "05", "06", 13, 83, 84, "2A", "2B"),
            'institutionName' => 'Université de Nice Sophia-Antipolis',
            'address' => 'Avenue Joseph Vallot',
            'zip' => '06108',
            'city' => 'NICE CEDEX 2',
            'phoneNumber' => '04 92 07 67 26',
            'faxNumber' => '04 92 07 67 00',
            'email' => 'urfist@unice.fr',
            'website' => 'http://urfist.unice.fr'
          ),
          'Bordeaux' => array(
            'departments' => array(24, 33, 40, 47, 64, 19, 23, 87, 16, 17, 79, 86),
            'address' => '4 avenue Denis Diderot',
            'bp' => 'CS 70051',
            'zip' => '33607',
            'city' => 'PESSAC CEDEX',
            'phoneNumber' => '05 56 84 29 19',
            'faxNumber' => '05 56 84 86 96',
            'email' => 'urfist@u-bordeaux.fr',
            'website' => 'http://weburfist.univ-bordeaux.fr'
          ),
          'Strasbourg' => array(
            'departments' => array(67, 68, 25, 39, 70, 90, 54, 55, 57, 88),
            'address' => '34 boulevard de la Victoire',
            'zip' => '67070',
            'city' => 'STRASBOURG',
            'phoneNumber' => '03 68 85 08 00',
            'faxNumber' => '03 68 85 08 19',
            'email' => 'urfist@u-strasbg.fr',
            'website' => 'http://urfist.u-strasbg.fr'
          ),
          'Paris' => array(
            'departments' => array(75, 77, 78, 91, 92, 93, 94, 95, 14, 50, 61, 27, 76, 18, 28, 36, 37, 41, 45, "08", 10, 51, 52, 973, 971, 972, 974, 976),
            'institutionName' => 'Ecole Nationale des Chartes',
            'address' => '17 rue des Bernardins',
            'zip' => '75005',
            'city' => 'PARIS',
            'phoneNumber' => '01 43 26 85 22',
            'faxNumber' => '01 56 24 97 33',
            'email' => 'secretariat.urfist@enc.sorbonne.fr',
            'website' => 'http://urfist.enc.sorbonne.fr'
          ),
          'Toulouse' => array(
            'departments' => array(11, 30, 34, 48, 66, "09", 12, 31, 32, 46, 65, 81, 82),
            'institutionName' => 'Maison de la Recherche et de la Valorisation',
            'address' => '118 route de Narbonne',
            'zip' => '31062',
            'city' => 'TOULOUSE CEDEX 9',
            'phoneNumber' => '05 62 25 00 82',
            //'faxNumber' => '',
            'email' => 'urfist@univ-toulouse.fr',
            'website' => 'http://urfist.univ-toulouse.fr'
          )
        );

        $index = 0;
        foreach($data as $ville => $fields) {
            $organization = new Organization();
            $organization->setId(++$index);
            $organization->setName('URFIST de ' . $ville);
            $organization->setCode(strtolower($ville));
            $organization->setAddressType(true);
            foreach($fields as $key => $value) {
                $accessor->setValue($organization, $key, $value);
            }
            $manager->persist($organization) ;
        }
        $manager->flush();
    }
}
