<?php
/**
 * Class used to create objects
 * Created by PhpStorm.
 * User: maxime
 * Date: 16/04/14
 * Time: 11:13
 */

namespace Sygefor\Bundle\TrainingBundle\Model;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Symfony\Component\Validator\Constraints\DateTime;

class SemesteredTraining
{

    /**
     * @var integer $year
     */
    private $year;

    /**
     * @var integer $semester
     */
    private $semester;

    /**
     * @var Training $training
     */
    private $training;

    /**
     * @var Session[] $session
     */
    private $sessions;


    public function __construct($year, $semester, $training, $sessions = null)
    {
        $this->year = $year;
        $this->semester = $semester;
        $this->training = $training;
        $this->setSessions($sessions);

    }

    /**
     * @param int $semester
     */
    public function setSemester($semester)
    {
        $this->semester = $semester;
    }

    /**
     * @return int
     */
    public function getSemester()
    {
        return $this->semester;
    }

    /**
     * @param array $sessions
     */
    public function setSessions($sessions = null)
    {
        if (is_array($sessions) && !empty($sessions)) {
            $this->sessions = $sessions;
            $this->orderSessions();
        } else {
            $this->setSessionsFromTrainingAndDate();
        }
    }

    /**
     * @return array
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param mixed $training
     */
    public function setTraining($training)
    {
        $this->training = $training;
    }

    /**
     * @return mixed
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->training->getId().'_'.$this->getYear().'_'.$this->getSemester();
    }

    /**
     * @return Session
     */
    public function getLastSession()
    {
        if (empty ($this->sessions)) return null;
        $now = new \DateTime();

        $result = null;
        $maxdif = 9999999999;
        foreach ($this->sessions as $session) {
            $dif = $now->getTimestamp() - $session->getDateBegin()->getTimeStamp();
            if (($dif > 0) && ($dif < $maxdif)) {
                $result = $session;
                $maxdif = $dif ;
            }
        }
        return $result;
    }

    /**
     * @return Session
     */
    public function getNextSession()
    {
        if (empty ($this->sessions)) return null;
        $now = new \DateTime();

        $result = null;
        $maxdif = 9999999999;
        foreach ($this->sessions as $session) {
            $dif = $session->getDateBegin()->getTimestamp() - $now->getTimeStamp();
            if (($dif > 0) && ($dif < $maxdif)) {
                $result = $session;
                $maxdif = $dif ;
            }
        }


        return $result;
    }

    /**
     * Returns the number of sessions belonging to semesteredtraining
     * @return int
     */
    public function getSessionsCount()
    {
        if (empty($this->sessions)) return 0;
        return count ($this->sessions);
    }

    /**
     * sets the sessions list given the current objects training and year/semester values
     */
    private function setSessionsFromTrainingAndDate()
    {

        $sessions = $this->training->getSessions();

        $tmpSessions = array();
        if (!empty ($sessions)) {
            foreach ($sessions as $session) {
                /** @var \DateTime $date */
                $date = $session->getDateBegin();

                $year = $date->format('Y');
                $semester = ($date->format('m') <= 6) ? 1 : 2;

                if ($year == $this->year && $semester == $this->semester) {
                    $tmpSessions [] = $session;
                }
            }

            $this->sessions = $tmpSessions;
            $this->orderSessions();
        }
    }

    /**
     * builds and returns SemesteredTraining objects array corresponding to Training object.
     * @param Training $training
     * @return array
     */
    public static function getSemesteredTrainingsForTraining(Training $training)
    {
        /** @var Session[] $sessions */
        $sessions = $training->getSessions();

        //sorting sessions per year/semester
        $orderedSessions = array();
        if ( count ($sessions) != 0 ){

            foreach($sessions as $session) {
                //if (!$session ){die();}
                /** @var \DateTime $date */
                $date = $session->getDateBegin();

                $year = $date->format('Y');
                $semester = ( $date->format('m') <= 6 ) ? 1 : 2 ;

                if (!isset($orderedSessions[$year])) {
                    $orderedSessions[$year] = array();
                }

                if (!isset($orderedSessions[$year][$semester])) {
                    $orderedSessions[$year][$semester] = array();
                }
                $orderedSessions[$year][$semester] []= $session;
            }

            $semTrainings= array ();

            //SemesteredTrainings objects are built around each sessions list
            foreach ($orderedSessions as $year => $semesters) {
                foreach ($semesters as $sem => $sessions) {
                    $tempSemTraining = new SemesteredTraining($year, $sem, $training);
                    $tempSemTraining->setSessions($sessions);

                    $semTrainings [] = $tempSemTraining;
                }
            }
            return $semTrainings;
        } else { // no session found : we build a single semestered training on first session year/semester.
            $year = $training->getFirstSessionPeriodYear();
            $semester = $training->getFirstSessionPeriodSemester();

            $semTraining = new SemesteredTraining($year, $semester, $training, array() );

            return array ($semTraining) ;
        }

    }

    /**
     * Returns an array of semestered trainings corresponding to given list of ids.
     * @param array $idList
     * @param EntityManager $em
     * @return SemesteredTraining[]
     */
    public static function getSemesteredTrainingsByIds(array $idList, EntityManager $em)
    {
        //building DQL query to get needed sessions objects
        $qb = $em->createQueryBuilder()
            ->select('s')
            ->from('SygeforTrainingBundle:Training', 't')
                ->leftJoin('SygeforTrainingBundle:Session', 's', Join::WITH, 't = s.training');

        $paramCount = 0;
        $parameters = array();
        foreach ($idList as $tId) {
            //var_dump($tId);
            $params = explode('_', $tId);

            if (count($params) == 3) {
                $dateFrom = ($params[2] == 2) ? $params[1] . '-01-07 00:00:00' : $params[1] . '-01-01 00:00:00';
                $dateTo = ($params[2] == 2) ? $params[1] . '-31-12 23:59:59' : $params[1] . '-30-06 23:59:59';

                $qb->orWhere('( t.id = :id' . $paramCount . ' AND s.dateBegin < :dateTo' . $paramCount . ' AND s.dateBegin > :dateFrom' . $paramCount . ')');
                $parameters = array_merge($parameters, array(
                    'id' . $paramCount => $params[0],
                    'dateTo' . $paramCount => $dateTo,
                    'dateFrom' . $paramCount => $dateFrom
                ));
                $paramCount++;
            }
        }

        //echo $qb->getQuery()->getDQL(); die();
        $qb->setParameters($parameters);
        $tmpArray = $qb->getQuery()->getResult();

        //objects are grouped by training / year / semester
        $sessions = array();
        foreach ($tmpArray as $re) {
            if (!empty ($re)) {
                $ys = SemesteredTraining::getYearAndSemesterFromDate($re->getDateBegin());
                $tId = $re->getTraining()->getId();
                if (!isset ($sessions[$tId])) {
                    $sessions[$tId] = array();
                }
                if (!isset ($sessions[$tId][$ys[0]])) {
                    $sessions[$tId][$ys[0]] = array();
                }
                if (!isset ($sessions[$tId][$ys[0]][$ys[1]])) {
                    $sessions[$tId][$ys[0]][$ys[1]] = array();
                }
                $sessions[$tId][$ys[0]][$ys[1]][] = $re;
            }
        }

        $semTrains = array();
        //for each training / year / semester, a SemesteredTraining object is built
        foreach ($idList as $id) {
            $params = explode('_', $id);

            if (count($params) == 3) {
                if (!empty ($sessions[$params[0]][$params[1]][$params[2]])) {

                    //getting sessions
                    $tmpSessions = $sessions[$params[0]][$params[1]][$params[2]];
                    $semTrains[] = new SemesteredTraining($params[1], $params[2], $tmpSessions[0]->getTraining(), $tmpSessions);
                } else {
                    $semTrains[] = new SemesteredTraining($params[1], $params[2],         $em->getRepository('SygeforTrainingBundle:Training')->find($params[0]), array());
                }
            }
        }
        //var_dump($qb->getQuery());
        return $semTrains;
    }

    /**
     * helper for getting year+ semester
     * @param \DateTime $date
     * @return array
     */
    public static function getYearAndSemesterFromDate(\DateTime $date)
    {
        $year = $date->format('Y');
        $semester = ( $date->format('m') <= 6 ) ? 1 : 2 ;

        return array($year, $semester);
    }

    /**
     * ordering
     *
     */
    private function orderSessions()
    {
         @usort ($this->sessions, function($a,$b){
            $ad = $a->getDateBegin();
            $bd = $b->getDateBegin();

            if ($ad == $bd) {
                return 0;
            }

            return $ad < $bd ? 1 : -1;
        });
    }
}
