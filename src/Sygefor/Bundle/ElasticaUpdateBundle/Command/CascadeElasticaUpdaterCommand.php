<?php
/**
 * Created by PhpStorm.
 * User: maxime
 * Date: 28/08/14
 * Time: 11:24
 */

namespace Sygefor\Bundle\ElasticaUpdateBundle\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sygefor\Bundle\ElasticaUpdateBundle\MappingProvider\MappingProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Doctrine\ORM\Mapping\ClassMetadata;


/**
 * Class CascadeElasticaUpdateCommand
 * @package Sygefor\Bundle\CoreBundle\Command
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
            ->addArgument('entityId', InputArgument::REQUIRED, 'Id de l\'entité concernée')
            ->addArgument('entityClass', InputArgument::REQUIRED, 'Classe de l\'entité concernée')
            ->addArgument('entityProperties', InputArgument::OPTIONAL, "Propriétés impactées (changeSet) - séparées par des virgules")
            ->addOption('show', null, InputOption::VALUE_NONE, 'Affiche simplement les stats, pas de rafraichissement');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * indicative example command : php app/console sygeforelasticascade:cascade 5 SygeforTrainingBundle:Session dateBegin
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('entityClass');
        $property = $input->getArgument('entityProperties') ? explode(',', $input->getArgument('entityProperties')) : array();
        $id = $input->getArgument('entityId');

        /** @var MappingProvider $mappingProvider */
        $mappingProvider = $this->getContainer()->get('sygefor_elastica_update.elastica_mapping_provider');
        $mappingProvider->findLinkedEntities($class, $property, $id, true);

        //updating index
        if (!$input->getOption('show')) {
            $mappingProvider->updateIndex();
        }

        if ($input->getOption('verbose') || $input->getOption('show') ) {
            $output->writeln("Updates:");
            $output->writeln($mappingProvider->getStats());
        }
    }
}
