<?php
namespace Sygefor\Bundle\ElasticaUpdateBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Sygefor\Bundle\ElasticaUpdateBundle\MappingProvider\MappingProvider;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class ElasticaCascadeUpdateListener
 * @package Sygefor\Bundle\ElasticaUpdateBundle\Listener
 */
class ElasticaCascadeUpdateListener implements EventSubscriber
{
    /** @var MappingProvider $mp */
    private $mp;

    /** @var  Kernel */
    private $kernel;

    protected $postFlushCommandLines = array();

    /**
     *
     */
    public function __construct(Kernel $kernel, MappingProvider $mp)
    {
        $this->mp = $mp;
        $this->kernel = $kernel;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
            Events::postPersist,
            Events::preUpdate,
            Events::postFlush
        ];
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $commands = $this->mp->getPostDeletionCommandLines($eventArgs->getEntity());

        foreach ($commands as $command) {
            $this->postFlushCommandLines[] = $command;
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        if(method_exists($eventArgs->getEntity(), 'getId')) {
            $this->postFlushCommandLines[] = array($eventArgs->getEntity()->getId(), get_class($eventArgs->getEntity()), $eventArgs->getEntity()->getId());
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs|\Doctrine\ORM\Event\PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if(method_exists($eventArgs->getEntity(), 'getId')) {
            // @todo : list modified properties
            $this->postFlushCommandLines[] = array($eventArgs->getEntity()->getId(), get_class($eventArgs->getEntity()), 'id');
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->postFlushCommandLines as $commandLine) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // windows
                // @todo not compatible
            } else {
                // prepare the process
                $env = $this->kernel->getEnvironment();
                $args = array_merge(array('nohup', 'php', '../app/console', '--env='.$env, 'sygeforelasticascade:cascade'), $commandLine);
                $pb = new ProcessBuilder($args);
                $process = $pb->getProcess();
                // add ampersand
                $process->setCommandLine($process->getCommandLine(). ' &');
                // run
                $process->run();
                if (!$process->isSuccessful()) {
                    throw new \RuntimeException($process->getErrorOutput());
                }
            }
        }
    }

}
