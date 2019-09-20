<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing\Test;

use IBekiaris\LoadBalancing\FakeComputingResource;
use IBekiaris\LoadBalancing\ResourceId;
use IBekiaris\LoadBalancing\WeightMap;
use PHPUnit\Framework\TestCase;

class WeightMapTest extends TestCase
{
    public function testEqual()
    {
        $resourcesIds = [
            '70973d9a-95ae-11e9-8398-91f0847efb5d',
            '7098bbde-95ae-11e9-aba6-2d9008b0e4b6',
            '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8',
            '709911f6-95ae-11e9-bf02-95b68eb31d60'
        ];

        $resources = array_map(function (string $resourceId) {
            return new FakeComputingResource(ResourceId::fromString($resourceId));
        }, $resourcesIds);

        $weightMap = WeightMap::equal($resources);
        $this->assertEquals(4, $weightMap->totalWeights());
        $this->assertEquals(1, $weightMap->maxWeight());
        $this->assertEquals(1, $weightMap->weightGDC());
    }

    public function testMaxWeight()
    {
        $weights = [
            '70973d9a-95ae-11e9-8398-91f0847efb5d' => 3,
            '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 10,
            '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 2,
            '709911f6-95ae-11e9-bf02-95b68eb31d60' => 4
        ];

        $weightMap = array_keys($weights);
        $weightMap = new WeightMap($weightMap);

        $this->assertEquals(10, $weightMap->maxWeight());
    }

    public function testTotalWeights()
    {
        $weights = [
            '70973d9a-95ae-11e9-8398-91f0847efb5d' => 3,
            '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 10,
            '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 2,
            '709911f6-95ae-11e9-bf02-95b68eb31d60' => 4
        ];

        $weightMap = array_keys($weights);
        $weightMap = new WeightMap($weightMap);

        $this->assertEquals(19, $weightMap->totalWeights());
    }
}
