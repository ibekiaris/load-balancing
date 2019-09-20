<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

use Psr\SimpleCache\CacheInterface;

final class WeightedRoundRobin implements LoadBalancingStrategy
{
    const STATE = 'algorithm_state';

    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function pick(Cluster $cluster): ComputingResource
    {
        $totalResource = $cluster->count();
        $maxWeight = $cluster->getWeightMap()->maxWeight();
        $gcd = $cluster->getWeightMap()->weightGDC();

        if (!$state = $this->cache->get(self::STATE)) {
            $state = [
                'current_weight' => 0,
                'current_iteration' => -1
            ];
        }

        $cw = $state['current_weight'];
        $i = $state['current_iteration'];

        $i = ($i + 1) % $totalResource;

        $currentResource = $cluster->resourceByNumeric($i);

        if (0 == $i) {
            $cw = $cw - $gcd;

            if ($cw <= 0) {
                $cw = $maxWeight;

                if ($cw == 0) {
                    throw new \InvalidArgumentException('Not weighted cluster');
                }
            }
        }

        $this->cache->set(self::STATE, [
            'current_weight' => $cw,
            'current_iteration' => $i
        ]);

        if ($currentResource->weight() >= $cw) {
            return $currentResource;
        }

        return $this->pick($cluster);
    }
}
