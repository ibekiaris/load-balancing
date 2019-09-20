<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

final class Cluster implements \Countable, \Iterator
{
    /**
     * @var array<int,ComputingResource>
     */
    private $resources = [];
    /**
     * @var array<string,int>
     */
    private $keyPairs = [];
    private $weightMap;
    private $position = 0;
    private $counter = 0;

    public function __construct(array $resource, WeightMap $weightMap)
    {
        $this->weightMap = $weightMap;
        array_walk($resource, function (ComputingResource $resource) use ($weightMap) {
            $this->add($resource, $weightMap);
        });
    }

    private function add(ComputingResource $resource, WeightMap $weightMap): void
    {
        $resourceIdObj = $resource->cResourceIdentifier();
        $resourceId = $resourceIdObj->toString();
        $weight = $weightMap->byKey($resourceIdObj);

        if (!isset($this->keyPairs[$resourceId])) {
            $this->resources[] = $resource->withWeight($weight);
            $this->keyPairs[$resourceId] = $this->counter++;
        }
    }

    public function contains(ComputingResource $resource): bool
    {
        return isset($this->keyPairs[$resource->cResourceIdentifier()->toString()]);
    }

    public function resourcesIds(): array
    {
        return array_keys($this->keyPairs);
    }

    public function id(): string
    {
        return implode('-', $this->resourcesIds());
    }

    /**
     * @throws ResourceNotFound
     */
    public function resource(ResourceId $resourceId): ComputingResource
    {
        $resourceIdAsString = $resourceId->toString();

        $numericKey = $this->keyPairs[$resourceIdAsString] ?? null;

        if (null === $numericKey) {
            throw new ResourceNotFound(sprintf('Resource with id %s not found', $resourceIdAsString));
        }

        $this->position = $numericKey;
        return $this->resources[$numericKey];
    }

    /**
     * @throws ResourceNotFound
     */
    public function resourceByNumeric(int $key): ComputingResource
    {
        if (!$resource = ($this->resources[$key] ?? null)) {
            throw new ResourceNotFound(sprintf('Resource with key %s not found', $key));
        }

        $this->position = $key;
        return $resource;
    }

    public function count(): int
    {
        return $this->counter;
    }

    public function random(): ComputingResource
    {
        $this->position = rand(0, $this->count() - 1);
        return $this->resources[$this->position];
    }

    public function current(): ComputingResource
    {
        return $this->resources[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->resources[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function getWeightMap(): WeightMap
    {
        return $this->weightMap;
    }
}
