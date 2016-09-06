<?php
/**
 * Created by PhpStorm.
 * User: Erwan
 * Date: 14/12/2015
 * Time: 11:32
 */

namespace Sygefor\Bundle\TrainerBundle\Command;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Sygefor\Bundle\ListBundle\Entity\Email;
use Sygefor\Bundle\TrainerBundle\Entity\Participation;
use Sygefor\Bundle\TrainerBundle\Entity\Trainer;
use Sygefor\Bundle\TrainingBundle\Entity\Session;
use Sygefor\Bundle\TrainingBundle\Entity\Training;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class SupervisorConvertCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $arrayLabelsToIds;

    /**
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->fillArrayLabelsToIds();
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('sygefor:supervisor:convert')
            ->setDescription('Convert supervisor to trainers');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getContainer()->get('doctrine')->getManager()->createQueryBuilder();
        $qb->select('t.id, t._supervisor')->distinct(true)->from('SygeforTrainingBundle:Training', 't')->where('t._supervisor is not null');
        $results = $qb->getQuery()->execute();
        foreach ($results as $training) {
            $supervisors = $training['_supervisor'];
            /** @var Training $training */
            $training = $this->getContainer()->get('doctrine')->getManager()->getRepository('SygeforTrainingBundle:Training')->find($training['id']);
            $delimiter = ',';
            if (strstr($supervisors, ';')) {
                $delimiter = ';';
            }
            else if (strstr($supervisors, ' et ')) {
                $supervisors = str_replace(' et ', ', ', $supervisors);
            }

            $supervisors = explode($delimiter, $supervisors);
            foreach ($supervisors as $supervisor) {
                $supervisor = trim($supervisor);
                if (!isset($this->arrayLabelsToIds[$supervisor])) {
                    throw new \Exception("Not found supervisor $supervisor");
                }
                if ($this->arrayLabelsToIds[$supervisor] !== 0) {
                    $trainer = $this->getContainer()->get('doctrine')->getManager()->getRepository('SygeforTrainerBundle:Trainer')->find($this->arrayLabelsToIds[$supervisor]);
                    $training->addSupervisor($trainer);
                }
            }
        }
        $this->getContainer()->get('doctrine')->getManager()->flush();
    }

    protected function fillArrayLabelsToIds()
    {
        $this->arrayLabelsToIds = array(
            'Aline Bouchard' => 41,
            'Aline BOUCHARD' => 41,
            'Manuel DURAND-BARTHEZ' => 23,
            'Manuel Durand-Barthez' => 23,
            'Manuel' => 23,
            'durand-barthez' => 23,
            'Christophe BOUDRY' => 1,
            'Christophe Boudry' => 1,
            'boudry' => 1,
            'Annaïg MAHE' => 4,
            'AnnaÎ MAHE' => 4,
            'Corinne Habarou' => 34,
            'Claire Denecker' => 104,
            'Claire DENECKER' => 104,
            'Chérifa BOUKACEM' => 127,
            'Marie-Laure Malingre' => 102,
            'Maie-Laure Malingre' => 102,
            'Alexandre Serres' => 166,
            'Jean-Paul VILLETTE' => 511,
            'Jean Paul VILLETTE' => 511,
            'Noël Thiboud' => 494,
            'Elsa Poupardin' => 476,
            'Michel Roland' => 72,
            'Pierre Ratinaud' => 644,
            'Julie Pierson' => 690,
            'Sabrina Granger' => 358,
            'Christelle Vallée' => 711,
            'Gabriel Gallezot' => 73,
            'Florence Garelli' => 707,
            'Iannis Aliferis' => 710,
            'Marie Didier' => 712,
            'Clémence Martin' => 713,
            'Isabelle Picault' => 714,
            'Nathalie Dremeau' => 715,
            'Chantal Maton-Elie' => 716,
            'Christiane Montis' => 717,
            'Michel Beney' => 718,
            'Danielle Charles-Le Bihan' => 719,
            'Maryline Ricaud' => 720,
            'Vincent Hanquiez' => 705,
            'Clément Coutelier' => 706,
            'Chérifa Boukacem Zeghmouri' => 127,
            'Nathalie Marcerou-Ramel' => 0,
            'Christelle Delaisse' => 0,
            'Chrystèle Delaisse' => 0,
            'Elysabeth BEYLS' => 0,
            'b' => 0
        );
    }

}