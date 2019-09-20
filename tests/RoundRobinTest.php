<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing\Test;

use IBekiaris\LoadBalancing\Cluster;
use IBekiaris\LoadBalancing\FakeComputingResource;
use IBekiaris\LoadBalancing\ResourceId;
use IBekiaris\LoadBalancing\RoundRobin;
use IBekiaris\LoadBalancing\WeightMap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;

class RoundRobinTest extends TestCase
{
    /**
     * @var Cluster
     */
    private $cluster;
    private $expected = [];

    public function setUp()
    {
        $this->expected = [
            '70973d9a-95ae-11e9-8398-91f0847efb5d',
            '7098bbde-95ae-11e9-aba6-2d9008b0e4b6',
            '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8',
            '709911f6-95ae-11e9-bf02-95b68eb31d60'
        ];

        $expected = array_map(function (string $resourceId) {
            return new FakeComputingResource(ResourceId::fromString($resourceId));
        }, $this->expected);

        $this->cluster = new Cluster($expected, WeightMap::equal($expected));
    }

    public function testAlgorithm()
    {
        $roundRobin = new RoundRobin(new ArrayCache());

        $actual = [];
        $iterations = range(1, rand(20,40), 1);

        $startedKey = null;
        foreach ($iterations as $iteration) {
            $resource = $roundRobin->pick($this->cluster);

            if (null === $startedKey) {
                $startedKey = $this->cluster->key();
            }

            $actual[] = $resource->cResourceIdentifier()->toString();
        }

        $this->assertResult($actual, $this->expected, $startedKey);
    }

    private function assertResult($actual, $expected, $startedKey)
    {
        foreach ($actual as $item) {
            $this->assertEquals($item, $expected[$startedKey]);
            $startedKey++;
            if ($startedKey > count($expected) - 1) {
                $startedKey = 0;
            }
        }
    }
}
