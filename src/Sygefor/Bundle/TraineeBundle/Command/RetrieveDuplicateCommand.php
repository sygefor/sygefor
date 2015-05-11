<?php
namespace Sygefor\Bundle\TraineeBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\Expr;
use Sygefor\Bundle\ElasticaUpdateBundle\MappingProvider\MappingProvider;
use Sygefor\Bundle\TraineeBundle\Entity\Trainee;
use Sygefor\Bundle\TraineeBundle\Entity\TraineeDuplicate;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class RetrieveDuplicateTraineesCommand
 * @package Sygefor\Bundle\CoreBundle\Command
 */
class RetrieveDuplicateCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('sygefor:trainee:duplicate')
            ->setDescription('Retrieve and mark duplicate trainees.')
            ->addOption('debug', null, InputOption::VALUE_NONE, "N'enregistre pas le marqueur de duplication.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $qb = $this->em->getRepository('SygeforTraineeBundle:Trainee')->createQueryBuilder('trainee');

        $qb_count = clone $qb;
        $qb_count->select($qb_count->expr()->count('trainee.id'));
        $count = $qb_count->getQuery()->getSingleScalarResult();

        $result = $qb->getQuery()->iterate();
        $progress = $this->getHelperSet()->get('progress');

        $progress->start($output, $count);

        $visited = array();
        foreach ($result as $row) {
            $trainee = $row[0];
            foreach($visited as $_trainee) {
                $this->compare($trainee, $_trainee);
            }
            array_push($visited, $trainee);
            $progress->advance();
            $this->em->flush();
            $this->em->clear();
        }

        $progress->finish();
    }

    /**
     * Compare to trainees
     * @param Trainee $t1
     * @param Trainee $t2
     */
    protected function compare($t1, $t2)
    {
        $type = "";
        if($this->isDuplicate($t1, $t2, $type)) {
            // need to merge entities (because of flush)
            $t1 = $this->em->merge($t1);
            $t2 = $this->em->merge($t2);

            $this->addDuplicate($t1, $t2, $type);
            $this->addDuplicate($t2, $t1, $type);
        }
    }

    /**
     * Trainees are duplicate ?
     *
     * @param Trainee $t1
     * @param Trainee $t2
     * @param string $type
     * @return bool
     */
    protected function isDuplicate($t1, $t2, &$type)
    {
        // if same organization, false
        if($t1->getOrganization() != $t2->getOrganization()) {
            return false;
        }

        // nom & prenom
        if($this->compareString($t1->getFullName(), $t2->getFullName())) {
            $type = "homonyme";
            return true;
        }

        // email
        // if($this->compareEmail($t1->getEmail(), $t2->getEmail())) {
        //     $type = "courriel";
        //     return true;
        // }

        // telephone
        //if($this->comparePhoneNumber($t1->getPhoneNumber(), $t2->getPhoneNumber())) {
        //    $type = "telephone";
        //    return true;
        //}

        return false;
    }

    /**
     * @param $string1
     * @param $string2
     * @return bool
     */
    protected function compareString($string1, $string2)
    {
        return $string1 && (strcasecmp($string1, $string2) == 0);
    }

    /**
     * @param $mail1
     * @param $mail2
     * @return bool
     */
    protected function compareEmail($mail1, $mail2)
    {
        return ($mail1 && ($mail1 == $mail2));
    }

    /**
     * @param $phone1
     * @param $phone2
     * @return bool
     */
    protected function comparePhoneNumber($phone1, $phone2)
    {
        $phone1 = preg_replace('/[^\d]/', "", $phone1);
        $phone2 = preg_replace('/[^\d]/', "", $phone2);
        if($phone1) {
            if($phone1 == $phone2) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Trainee $t1
     * @param Trainee $t2
     */
    protected function addDuplicate($t1, $t2, $type = "")
    {
        if ($t1->getAllDuplicates()) {
            $duplicateList = $t1->getAllDuplicates()->filter(function ($duplicate) use ($t2) {
                return $duplicate->getTraineeTarget()->getId() == $t2->getId();
            });
        }

        if ($t1->getAllDuplicates() && count($duplicateList) == 0) {
            $duplicate = new TraineeDuplicate();
            $this->em->persist($duplicate);
            $duplicate->setTraineeSource($t1);
            $duplicate->setTraineeTarget($t2);
            $duplicate->setIgnored(false);
            $duplicate->setType($type);
        }
    }
}
