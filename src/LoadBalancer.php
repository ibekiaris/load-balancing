<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

interface LoadBalancer
{
    public function pick(Cluster $cluster): ComputingResource;
}
