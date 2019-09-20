<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

final class FakeComputingResource implements ComputingResource
{
    private $resourceId;
    private $weight;

    public function __construct(ResourceId $resourceId, int $weight = 1)
    {
        $this->resourceId = $resourceId;
        $this->weight = $weight;
    }

    public function cResourceIdentifier(): ResourceId
    {
        return $this->resourceId;
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function withWeight(int $weight): ComputingResource
    {
        $self = clone $this;
        $self->weight = $weight;
        return $self;
    }
}
