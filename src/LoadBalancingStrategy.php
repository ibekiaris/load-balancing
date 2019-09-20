<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

interface LoadBalancingStrategy
{
    public function pick(Cluster $cluster): ComputingResource;
}
