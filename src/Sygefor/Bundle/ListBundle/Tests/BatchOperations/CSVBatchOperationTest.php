<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 09/04/14
 * Time: 10:22
 */
namespace Sygefor\Bundle\ListBundle\Tests\BatchOperations;

//use Doctrine\DBAL\Query\QueryBuilder;

use Sygefor\Bundle\ListBundle\BatchOperations\CSVBatchOperation;

class CSVBatchOperationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testOperationExportsAllUsers()
    {
        $this->markTestSkipped('je sais pas comment récupérer la sortie...Il manque le security context dans le construct');
        $csvBatch = new CSVBatchOperation($this->getMockedEntityManager(),null);

        $csvBatch->setOptions(
            array(
                'fields' => array(
                    'username' => array('label' => 'Nom'),
                    'email' => array('label' => 'Email'),
                    'organization.name' => array('label' => 'URFIST')
                )
            )
        );
        ob_start();
        $csvBatch->execute(array('4', '8', '10'));
        ob_end_flush();

    }

    private function getMockedEntityManager()
    {
        $org1 = $this->getMockedOrganization('Org 1');

        $org2 = $this->getMockedOrganization('Org 2');
        $entities = array(
            $this->getMockedUser('User1', 'u1@mail.com', $org1),
            $this->getMockedUser('User2', 'u2@mail.com', $org2),
        );

        $mock = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();

        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->setMethods(array('setParameter', 'getResult'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $query->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue($entities));


        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $queryBuilder->expects($this->any())
            ->method('select')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())
            ->method('from')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())
            ->method('where')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->any())
            ->method('setParameter')
            ->will($this->returnValue($queryBuilder));


        $queryBuilder->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $mock->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        return $mock;
    }

    private function getMockedUser($name, $email, $org)
    {
        $user = $this->getMock('Sygefor\Bundle\UserBundle\Entity\User');
        $user->expects($this->any())
            ->method('getUserName')
            ->will($this->returnValue($name));

        $user->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue($email));
        $user->expects($this->any())
            ->method('getOrganization')
            ->will($this->returnValue($org));

        return $user;
    }

    private function getMockedOrganization($name)
    {
        $org = $this->getMock('Sygefor\Bundle\CoreBundle\Entity\Organization');
        $org->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $org;
    }

} 