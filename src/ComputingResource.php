<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

interface ComputingResource
{
    public function cResourceIdentifier(): ResourceId;

    public function weight(): int;

    public function withWeight(int $weight): ComputingResource;
}
