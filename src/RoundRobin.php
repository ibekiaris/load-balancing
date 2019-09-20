<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

use Psr\SimpleCache\CacheInterface;

final class RoundRobin implements LoadBalancingStrategy
{
    const NUMBER_OF_ITERATIONS_TILL_NOW = 'number_of_iterations';

    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @throws ResourceNotFound
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function pick(Cluster $cluster): ComputingResource
    {
        $totalNumberOfResources = $cluster->count();
        if (!$numberOfIterations = $this->cache->get(self::NUMBER_OF_ITERATIONS_TILL_NOW)) {
            // Start from a random computing resource
            $numberOfIterations = rand(0, $totalNumberOfResources - 1);
        }

        $resourceKey = ++$numberOfIterations % $totalNumberOfResources;
        $this->cache->set(self::NUMBER_OF_ITERATIONS_TILL_NOW, $numberOfIterations);
        return $cluster->resourceByNumeric($resourceKey);
    }
}
