<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 10/12/2015
 * Time: 12:34
 */

namespace Sygefor\Bundle\TrainerBundle\Command;


use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\ListBundle\Entity\Email;
use Sygefor\Bundle\TrainerBundle\Entity\Participation;
use Sygefor\Bundle\TrainerBundle\Entity\Term\CompetenceField;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TrainerMergeCommand
 * @package Sygefor\Bundle\TrainerBundle\Command
 */
class TrainerMergeCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $arrayIdsToMerge;

    /**
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->fillArrayIdsToMerge();
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('sygefor:trainer:merge')
            ->setDescription('Merge duplicate trainers');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, count($this->arrayIdsToMerge));
        foreach ($this->arrayIdsToMerge as $idToKeep => $idsToMerge) {
            /** @var Trainer $trainerToKeep */
            $trainerToKeep = $this->em->getRepository('SygeforTrainerBundle:Trainer')->find($idToKeep);
            foreach ($idsToMerge as $trainerIdToMerge) {
                /** @var Trainer $trainerToMerge */
                $trainerToMerge = $this->em->getRepository('SygeforTrainerBundle:Trainer')->find($trainerIdToMerge);
                if (!$trainerToMerge) {
                    throw new \Exception('Invalid id ' . $trainerIdToMerge);
                }
                $this->mergeTrainers($trainerToKeep, $trainerToMerge);
            }
            $progress->advance();
            $this->em->flush();
        }
        $progress->finish();
    }

    /**
     * @param Trainer $trainerToKeep
     * @param Trainer $trainerToMerge
     */
    protected function mergeTrainers(Trainer $trainerToKeep, Trainer $trainerToMerge)
    {
        /** @var CompetenceField $competenceField */
        foreach ($trainerToMerge->getCompetenceFields() as $competenceField) {
            if ($competenceField->getOrganization() === $trainerToKeep->getOrganization()) {
                $trainerToKeep->addCompetenceFields($competenceField);
            }
        }

        /** @var Session $session */
        foreach ($trainerToMerge->getSessions() as $session) {
            $session->removeTrainer($trainerToMerge);
            $session->addTrainer($trainerToKeep);
        }

        /** @var Participation $participation */
        foreach ($trainerToMerge->getParticipations() as $participation) {
            $newParticipation = new Participation();
            $newParticipation->setSession($participation->getSession());
            $newParticipation->setTrainer($trainerToKeep);
            $newParticipation->setIsUrfist($participation->getIsUrfist());
            $newParticipation->setOrganization($participation->getOrganization());
            $this->em->persist($newParticipation);
        }

        $emails = $this->em->getRepository('SygeforListBundle:Email')->findBy(array('trainer' => $trainerToMerge));
        /** @var Email $email */
        foreach ($emails as $email) {
            $newEmail = new Email();
            $newEmail->setUserFrom($email->getUserFrom());
            $newEmail->setEmailFrom($email->getEmailFrom());
            $newEmail->setSubject($email->getSubject());
            $newEmail->setBody($email->getBody());
            $newEmail->setSendAt($email->getSendAt());
            $newEmail->setTrainer($trainerToKeep);
            $this->em->persist($newEmail);
        }

        $this->em->remove($trainerToMerge);
    }

    /**
     * Fill an array with trainee to keep as key and trainee ids to merge as values
     */
    protected function fillArrayIdsToMerge()
    {
        $this->arrayIdsToMerge = array(
              // 08/01/16 merge
//            82 => [240],
//            23 => [563, 24, 110, 200, 297, 307, 446, 543, 612],
//            77 => [94, 178, 261, 501, 538, 592, 636],
//            30 => [118, 216, 292, 471, 560, 628, 304],
//            38 => [321, 52],
//            18 => [206, 466, 467],
//            22 => [249, 463],
//            203 => [481],
//            175 => [601, 519],
//            132 => [363, 502, 577, 650],
//            7 => [187, 314, 455],
//            98 => [327],
//            70 => [311],
//            116 => [477],
//            133 => [365],
//            226 => [338],
//            100 => [431],
//            41 => [160, 231, 390, 586, 640],
//            626 => [629],
//            145 => [580],
//            235 => [637],
//            40 => [105, 217],
//            46 => [92],
//            379 => [413],
//            197 => [472],
//            112 => [284, 549],
//            148 => [215, 385],
//            104 => [512, 581],
//            53 => [120, 208, 488],
//            114 => [312],
//            103 => [193, 170, 318, 523,553],
//            86 => [370, 620],
//            380 => [595],
//            223 => [294],
//            5 => [113, 273, 578, 220],
//            73 => [143, 177, 486, 521, 605],
//            182 => [302],
//            25 => [219],
//            236 => [366],
//            47 => [209, 479],
//            205 => [448],
//            653 => [674],
//            174 => [537, 548],
//            34 => [416, 508],
//            11 => [195, 356, 422, 530],
//            84 => [202, 424],
//            536 => [566],
//            351 => [574],
//            109 => [248],
//            80 => [238, 398, 648, 155, 513],
//            83 => [473],
//            190 => [256, 483],
//            10 => [211, 562],
//            32 => [590, 593],
//            2 => [176],
//            158 => [641],
//            12 => [194, 465],
//            164 => [389],
//            4 => [604, 115, 186, 305, 440, 534, 652],
//            62 => [278],
//            102 => [165, 288, 464, 540],
//            126 => [224],
//            153 => [497, 575],
//            135 => [492],
//            128 => [573],
//            320 => [443],
//            260 => [572],
//            21 => [144, 212],
//            272 => [514],
//            31 => [125, 227, 469],
//            124 => [315, 210, 478],
//            257 => [555],
//            64 => [150, 399, 635],
//            39 => [119, 234, 331, 480],
//            199 => [447],
//            184 => [421],
//            72 => [151, 192, 285, 460, 529, 610],
//            20 => [286],
//            166 => [287, 487],
//            218 => [374],
//            134 => [229, 579],
//            225 => [349, 495],
//            263 => [458],
//            26 => [420],

            // 11/01/16 merge
            417 => [727],
            241 => [700],
            695 => [721],
            66 => [676],
            78 => [688, 689, 699]
        );
    }
}