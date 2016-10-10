<?php

namespace Sygefor\Bundle\CoreBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Sygefor\Bundle\CoreBundle\MappingProvider\MappingProvider;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class ElasticaCascadeUpdateListener.
 */
class ElasticaCascadeUpdateListener implements EventSubscriber
{
    /** @var MappingProvider $mp */
    private $mp;

    /** @var  Kernel */
    private $kernel;

    protected $postFlushArguments = array();

    /**
     *
     */
    public function __construct(Kernel $kernel, MappingProvider $mp)
    {
        $this->mp     = $mp;
        $this->kernel = $kernel;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::preRemove,
            Events::postPersist,
            Events::preUpdate,
            Events::postFlush,
        );
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $commands = $this->mp->getPostDeletionCommandLines($eventArgs->getEntity());

        foreach ($commands as $command) {
            $this->postFlushArguments[] = $command;
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        if (method_exists($eventArgs->getEntity(), 'getId')) {
            $this->postFlushArguments[] = array($eventArgs->getEntity()->getId(), get_class($eventArgs->getEntity()), $eventArgs->getEntity()->getId());
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs|\Doctrine\ORM\Event\PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if (method_exists($eventArgs->getEntity(), 'getId')) {
            // @todo : list modified properties
            $this->postFlushArguments[] = array($eventArgs->getEntity()->getId(), get_class($eventArgs->getEntity()), 'id');
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        $idsByClass     = array();
        $propertiesById = array();

        // refactor arguments to send them to console command
        foreach ($this->postFlushArguments as $arguments) {
            $idsByClass[$arguments[1]][]   = $arguments[0];
            $propertiesById[$arguments[0]] = $arguments[2];
        }

        $appDir = $this->kernel->getRootDir();
        $env    = $this->kernel->getEnvironment();
        foreach ($idsByClass as $class => $ids) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $process = new Process(
                    'php ' . $appDir . "/console --env=$env sygeforelasticascade:cascade $class " . json_encode($ids) . ' ' . json_encode($propertiesById)
                );
                $process->run();
                if ( ! $process->isSuccessful()) {
                    throw new \RuntimeException($process->getErrorOutput());
                }
            }
            else {
                // prepare the process
                $env  = $this->kernel->getEnvironment();
                $args = array(
                    'nohup',
                    'php',
                    $appDir . '/console',
                    '--env=' . $env,
                    'sygeforelasticascade:cascade',
                    $class,
                    json_encode($ids),
                    json_encode($propertiesById),
                );

                $pb      = new ProcessBuilder($args);
                $process = $pb->getProcess();
                $process->setCommandLine($process->getCommandLine() . ' &'); // add ampersand

                // run process
                $process->start();
//                if (!$process->isSuccessful()) {
//                    throw new \RuntimeException($process->getErrorOutput());
//                }
            }
        }
    }
}
