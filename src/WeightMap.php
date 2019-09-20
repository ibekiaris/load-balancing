<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

final class WeightMap
{
    /**
     * @var array<string,int>
     */
    private $container = [];
    private $totalWeights = 0;
    private $maxWeight = 0;
    private $gcd = 0;

    /**
     * @param array<string,int> $resoucesWeighs
     */
    public function __construct(array $resourcesWeighs)
    {
        /**
         * @var string $resourceId
         * @var int $weigh
         */
        foreach ($resourcesWeighs as $resourceId => $weigh) {
            $resourceIdObj = ResourceId::fromString($resourceId);
            $this->mapValue($resourceIdObj, $weigh);
        }
    }

    /**
     * @return array<string, int>
     */
    public function values(): array
    {
        return $this->container;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function byKey(ResourceId $resourceId): int
    {
        /** @var int|null $weight */
        $weight = $this->container[$resourceId->toString()] ?? null;

        if (null === $weight) {
            throw new \InvalidArgumentException('No weight defined');
        }

        return $weight;
    }

    /**
     * @param array $resources
     */
    public static function equal(array $resources): WeightMap
    {
        $weightMap = new WeightMap([]);
        array_walk($resources, function (ComputingResource $resource) use ($weightMap) {
            $weightMap->mapValue($resource->cResourceIdentifier(), 1);
        });

        return $weightMap;
    }

    public function maxWeight(): int
    {
        return $this->maxWeight;
    }

    public function totalWeights(): int
    {
        return $this->totalWeights;
    }

    public function weightGDC(): int
    {
        return $this->gcd;
    }

    private function mapValue(ResourceId $resourceId, int $weight)
    {
        if (!isset($this->container[$resourceId->toString()])) {
            $this->gcd = $this->gcd($weight, $this->gcd);
            $this->totalWeights += $weight;
            if ($weight > $this->maxWeight) {
                $this->maxWeight = $weight;
            }
            $this->container[$resourceId->toString()] = $weight;
        }
    }

    private function gcd($a,$b ) {

        if ($b == 0) {
            return $a;
        }
        
        $large = $a > $b ? $a: $b;
        $small = $a > $b ? $b: $a;
        $remainder = $large % $small;
        return 0 == $remainder ? $small : $this->gcd( $small, $remainder );
    }
}
