<?php

/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/08/14
 * Time: 11:24.
 */
namespace Sygefor\Bundle\CoreBundle\Command;

use Sygefor\Bundle\CoreBundle\MappingProvider\MappingProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CascadeElasticaUpdateCommand.
 */
class CascadeElasticaUpdaterCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this->visitedEntities = array();

        $this->setName('sygeforelasticascade:cascade')
            ->addArgument('entityClass', InputArgument::REQUIRED, 'Classe de l\'entité concernée')
            ->addArgument('entityIds', InputArgument::OPTIONAL, 'Id des entités concernées')
            ->addArgument('entityProperties', InputArgument::OPTIONAL, 'Propriétés impactées (changeSet) - séparées par des virgules')
            ->addOption('show', null, InputOption::VALUE_NONE, 'Affiche simplement les stats, pas de rafraichissement');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *                       indicative example command : php app/console sygeforelasticascade:cascade [1188] SygeforTrainingBundle:Session {1188: [dateBegin]}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MappingProvider $mappingProvider */
        $mappingProvider = $this->getContainer()->get('sygefor_core.elastica_mapping_provider');

        $class      = $input->getArgument('entityClass');
        $allIds     = $input->getArgument('entityIds') ? json_decode($input->getArgument('entityIds'), true) : array();
        $properties = $input->getArgument('entityProperties') ? json_decode($input->getArgument('entityProperties'), true) : array();

        $mappingProvider->findLinkedEntities($class, null, $allIds, true);

        //updating index
        if ( ! $input->getOption('show')) {
            $mappingProvider->updateIndex();
        }

        if ($input->getOption('verbose') || $input->getOption('show') ) {
            $output->writeln('Updates:');
            $output->writeln($mappingProvider->getStats());
        }
    }
}
