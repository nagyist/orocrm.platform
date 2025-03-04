<?php

namespace Oro\Bundle\DistributionBundle\Routing;

use Oro\Bundle\DistributionBundle\Event\RouteCollectionEvent;
use Oro\Component\Routing\Loader\CumulativeRoutingFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Improves performance of loading of routing collection.
 */
abstract class AbstractLoader extends CumulativeRoutingFileLoader
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var SharedData */
    protected $cache;

    /**
     * Sets the event dispatcher
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Sets an object that can be used to share data between different loaders
     */
    public function setCache(?SharedData $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $eventName
     * @param RouteCollection $routes
     * @return RouteCollection
     */
    protected function dispatchEvent($eventName, RouteCollection $routes)
    {
        if (!$this->eventDispatcher) {
            return $routes;
        }

        $event = new RouteCollectionEvent($routes);
        $this->eventDispatcher->dispatch($event, $eventName);

        return $event->getCollection();
    }

    #[\Override]
    protected function loadRoutes(RouteCollection $routes)
    {
        if (null === $this->cache) {
            parent::loadRoutes($routes);
        } else {
            $resources = $this->getResources();
            foreach ($resources as $resource) {
                if (is_string($resource)) {
                    $resourceRoutes = $this->cache->getRoutes($resource);
                    if (null === $resourceRoutes) {
                        $resourceRoutes = $this->import($resource);
                        $this->updateRoutesPriority($resourceRoutes);
                        $this->cache->setRoutes($resource, $resourceRoutes);
                    }
                } else {
                    $resourceRoutes = $this->import($resource);
                    $this->updateRoutesPriority($resourceRoutes);
                }
                $routes->addCollection($resourceRoutes);
            }
        }
    }
}
