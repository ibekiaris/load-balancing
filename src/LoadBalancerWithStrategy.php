<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

abstract class LoadBalancerWithStrategy implements LoadBalancer
{
    private $strategy;

    public function __construct(LoadBalancingStrategy $balancingStrategy)
    {
        $this->strategy = $balancingStrategy;
    }

    public function pick(Cluster $cluster): ComputingResource
    {
        return $this->strategy->pick($cluster);
    }
}
