<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

interface WeightProvider
{
    public function resourcesWeight(): WeightMap;
}
