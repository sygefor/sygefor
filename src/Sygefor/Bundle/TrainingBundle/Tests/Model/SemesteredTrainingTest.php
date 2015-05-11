<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 23/04/14
 * Time: 17:00
 */
namespace Sygefor\Bundle\TrainingBundle\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Sygefor\Bundle\CoreBundle\Entity\Organization;
use Sygefor\Bundle\CoreBundle\Test\WebTestCase;
use Sygefor\Bundle\TrainingBundle\Entity\DiverseTraining;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Sygefor\Bundle\TrainingBundle\Model\SemesteredTraining;
use Symfony\Component\Form\Util\OrderedHashMap;

class SemesteredTrainingTest extends WebTestCase
{

    protected function setUp()
    {
        //die();

        parent::setUp();
    }

    /**
     * @dataProvider getSessionsList
     */
    public function testSemesteredTrainingStoresSessions($training, $year,$semester, $count, $id)
    {
        $semesteredTraining = new SemesteredTraining($year, $semester, $training);
        //$semesteredTraining->setSessions($sessions);

        $sessionsCount = $semesteredTraining->getSessionsCount();

        $this->assertEquals($count, $sessionsCount);
        $this->assertEquals($id, $semesteredTraining->getId());
    }

    /**
     * @dataProvider getTrainingWithSessions
     */
    public function testGetSemesteredTrainings($training, $count)
    {
        $semtrains = SemesteredTraining::getSemesteredTrainingsForTraining($training);

        $this->assertEquals(count ($semtrains), $count);
    }

    /**
     * @dataProvider getTrainingWithSessionsUsingDatesBeforeAfter
     */
    public function testLastAndNextSessionsAreFound($y, $s, $train, $sessIds)
    {

        $semtrains = SemesteredTraining::getSemesteredTrainingsForTraining($train);
        $semtrain = $semtrains[0];

        $this->assertEquals($sessIds[0], $semtrain->getLastSession()->getId());
        $this->assertEquals($sessIds[1], $semtrain->getNextSession()->getId());
    }

    /**
     * ??dataProvider getTrainingsAndSessions
     *
     */
//    public function testGetSemesteredTrainingByIds($trainingsAndSessions)
//    {
//        $result = SemesteredTraining::getSemesteredTrainingsByIds($trainingsAndSessions['ids'], $this->getEntityManager());
//
//        for ($i = 0; $i < count($trainingsAndSessions['ids']); $i++) {
//        $cnt = count ( $result[$i]->getSessions() );
//        $this->assertEquals($cnt, $trainingsAndSessions['count'][$i]);
//        }
//    }

    /**
     * @param Training $training
     * @param \DateTime $date
     * @param null $id
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedSession(Training $training, \DateTime $date, $id = null)
    {
        $session = $this->getMock('Sygefor\Bundle\TrainingBundle\Entity\Session');
        $session->expects($this->any())
            ->method('getTraining')
            ->will($this->returnValue($training));

        $session->expects($this->any())
            ->method('getDateBegin')
            ->will($this->returnValue($date));

        if ($id != null) {
            $session->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($id));
        }

        return $session;
    }

    /**
     * @param $id
     * @param $sessionsDates
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockedTraining($id, $sessionsDates)
    {
        $training = $this->getMock('Sygefor\Bundle\TrainingBundle\Entity\Training');
        $training->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        $sessions = array();
        foreach ($sessionsDates as $id =>$date) {
            $tmpSession = $this->getMockedSession($training, $date, $id);
            $sessions[] = $tmpSession;
        }

        $training->expects($this->any())
            ->method('getSessions')
            ->will($this->returnValue($sessions));

        return $training;
    }

    /**
     * used as dataprovider
     */
    public function getSessionsList()
    {

        $fooDateSession1 = new \DateTime('2009-11-11');
        $fooDateSession2 = new \DateTime('2012-04-08');
        $barDateSession1 = new \DateTime('2009-11-11');
        $barDateSession2 = new \DateTime('2012-04-04');
        $fooTrain = $this->getMockedTraining('foo', array($fooDateSession1, $fooDateSession2));
        $barTrain = $this->getMockedTraining('bar', array($barDateSession1, $barDateSession2));

        return array(
            array($fooTrain, 2009, 2, 1, 'foo_2009_2'),
            array($fooTrain, 2009, 1, 0, 'foo_2009_1'),
            array($barTrain, 2012, 1, 1, 'bar_2012_1'),
        );
    }


    /**
     * sends training and sessions
     * used as dataprovider
     */
    public function getTrainingsAndSessions()
    {
//        $organization = new Organization();
//        $organization->setName('Mon Org');
//        $organization->setCode('AAA');
//
//        $diverseTraining1 = new DiverseTraining();
//        $diverseTraining1->setName("TEST1") ;
//        $diverseTraining1->setFirstSessionPeriodSemester(1);
//        $diverseTraining1->setFirstSessionPeriodYear('2014');
//        $diverseTraining1->setOrganization($organization);
//
//        $session11 = new Session();
//        $session11->setLimitRegistrationDate(new \DateTime('2015-09-20'));
//        $session11->setDateBegin( new \DateTime('2015-07-20'));
//        $session11->setPublished(true);
//        $session11->setMaximumNumberOfRegistrations(12);
//        $session11->setTraining($diverseTraining1);
//
//        $session12 = new Session();
//        $session12->setPublished(true);
//        $session12->setLimitRegistrationDate(new \DateTime('2016-09-20'));
//        $session12->setDateBegin( new \DateTime('2016-07-20'));
//        $session12->setMaximumNumberOfRegistrations(12);
//        $session12->setTraining($diverseTraining1);
//
//        $diverseTraining1->setSessions(array($session11, $session12));
//
//        $diverseTraining2 = new DiverseTraining();
//        $diverseTraining2->setName("TEST2") ;
//        $diverseTraining2->setFirstSessionPeriodSemester(1);
//        $diverseTraining2->setFirstSessionPeriodYear('2014');
//        $diverseTraining2->setOrganization($organization);
//
//        $session21 = new Session();
//        $session21->setLimitRegistrationDate(new \DateTime('2015-09-20'));
//        $session21->setDateBegin( new \DateTime('2015-09-20'));
//        $session21->setPublished(true);
//        $session21->setMaximumNumberOfRegistrations(12);
//        $session21->setTraining($diverseTraining2);
//
//        $session22 = new Session();
//        $session22->setLimitRegistrationDate(new \DateTime('2016-05-20'));
//        $session22->setDateBegin( new \DateTime('2016-01-20'));
//        $session22->setPublished(true);
//        $session22->setMaximumNumberOfRegistrations(12);
//        $session22->setTraining($diverseTraining2);
//
//        $session23 = new Session();
//        $session23->setLimitRegistrationDate(new \DateTime('2016-03-20'));
//        $session23->setDateBegin( new \DateTime('2016-03-20'));
//        $session23->setPublished(true);
//        $session23->setMaximumNumberOfRegistrations(12);
//        $session23->setTraining($diverseTraining2);
//
//        $diverseTraining2->setSessions(array($session21, $session22, $session23));
//
//        if (!$this->client) {
//            $this->client = static::createClient();
//        }
//
//        $this->getEntityManager()->persist($organization);
//
//        $this->getEntityManager()->persist($session11);
//        $this->getEntityManager()->persist($session12);
//        $this->getEntityManager()->persist($diverseTraining1);
//
//        $this->getEntityManager()->persist($session21);
//        $this->getEntityManager()->persist($session22);
//        $this->getEntityManager()->persist($session23);
//        $this->getEntityManager()->persist($diverseTraining2);
//
//        $this->getEntityManager()->flush();
//
//        $idT1 = $diverseTraining1->getId();
//        $idT2 = $diverseTraining2->getId();

        return array(
//            array ( array('ids' => array($idT1 . '_2015_2', $idT1 . '_2016_2'), "count" => array(1, 1), 'training' => $diverseTraining1) ),
//            array ( array('ids' => array($idT2 . '_2015_2', $idT2 . '_2016_1'), "count" => array(1, 2), 'training' => $diverseTraining2) ),
        );
    }


    /**
     * used as dataprovider
     */
    public function getTrainingWithSessions()
    {

        $fooDateSession1 = new \DateTime('2009-11-11');
        $fooDateSession2 = new \DateTime('2012-04-08');
        $barDateSession1 = new \DateTime('2009-11-11');
        $barDateSession2 = new \DateTime('2012-04-04');
        $fooTrain = $this->getMockedTraining('foo', array($fooDateSession1, $fooDateSession2));
        $barTrain = $this->getMockedTraining('bar', array($barDateSession1, $barDateSession2));

        return array(
            array($fooTrain, 2),
            array($fooTrain, 2),
            array($barTrain, 2),
        );
    }

    /**
     * used as dataprovider
     */
    public function getTrainingWithSessionsUsingDatesBeforeAfter()
    {
        $date1 = new \DateTime();
        $date2 = clone $date1;
        $date1->modify('-1 day');
        $date2->modify('+1 day');
        $d = SemesteredTraining::getYearAndSemesterFromDate($date1);

        $train = $this->getMockedTraining('footrain', array('foosess' => $date1, 'barsess' => $date2));

        return array(
            array($d[0], $d[1], $train, array ( 'foosess','barsess' )),
        );
    }

}
