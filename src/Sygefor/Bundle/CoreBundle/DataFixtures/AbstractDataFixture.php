<?php

namespace Sygefor\Bundle\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Events;
use FOS\ElasticaBundle\Doctrine\Listener;
use Sygefor\Bundle\CoreBundle\Listener\ElasticaCascadeUpdateListener;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Provides support for environment specific fixtures.
 *
 * This container aware, abstract data fixture is used to only allow loading in
 * specific environments. The environments the data fixture will be loaded in is
 * determined by the list of environment names returned by `getEnvironments()`.
 */
abstract class AbstractDataFixture implements ContainerAwareInterface, FixtureInterface, OrderedFixtureInterface
{
    /**
     * The dependency injection container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var int
     */
    protected $autoId = 0;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->removeElasticaListeners($manager);

        /** @var KernelInterface $kernel */
        $kernel = $this->container->get('kernel');
        if ($this->getEnvironments() === true || in_array($kernel->getEnvironment(), $this->getEnvironments(), true)) {
            $this->doLoad($manager);
        }
    }

    /**
     * @param ObjectManager $manager
     *
     * Remove elastica indexing during the anonymization by removing elastica listeners.
     */
    protected function removeElasticaListeners(ObjectManager $manager)
    {
        $listeners = $manager->getEventManager()->getListeners(Events::postFlush);
        foreach ($listeners as $listener) {
            if ($listener instanceof Listener || $listener instanceof ElasticaCascadeUpdateListener) {
                $manager->getEventManager()->removeEventListener(Events::postFlush, $listener);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Performs the actual fixtures loading.
     *
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     *
     * @param ObjectManager $manager The object manager.
     */
    abstract protected function doLoad(ObjectManager $manager);

    /**
     * Returns the environments the fixtures may be loaded in.
     *
     * @return array The name of the environments.
     */
    protected function getEnvironments()
    {
        return true;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    function getOrder()
    {
        return 0;
    }
}
