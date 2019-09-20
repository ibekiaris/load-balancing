<?php declare(strict_types=1);

namespace IBekiaris;

use IBekiaris\LoadBalancing\Cluster;
use IBekiaris\LoadBalancing\FakeComputingResource;
use IBekiaris\LoadBalancing\ResourceId;
use IBekiaris\LoadBalancing\ResourceNotFound;
use IBekiaris\LoadBalancing\WeightMap;
use PHPUnit\Framework\TestCase;

class ClusterTest extends TestCase
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

    public function testIterator()
    {
        $actual = [];

        foreach ($this->cluster as $computingResource) {
            $actual[] = $computingResource->cResourceIdentifier()->toString();
        }

        $this->assertEquals($this->expected, $actual);
    }

    public function testContains()
    {
        $random = rand(0, count($this->expected) - 1);
        $expected = $this->expected[$random];

        $this->assertTrue(
            $this->cluster->contains(new FakeComputingResource(ResourceId::fromString($expected)))
        );
    }

    public function testResource()
    {
        $random = rand(0, count($this->expected) - 1);
        $expected = $this->expected[$random];

        $actual = $this->cluster->resource(ResourceId::fromString($expected));
        $this->assertEquals($expected, $actual->cResourceIdentifier()->toString());
    }

    public function testResourceNotFound()
    {
        $this->expectException(ResourceNotFound::class);
        $this->cluster->resource(ResourceId::fromString('77777777-7777-7777-7777-777777777777'));
    }

    public function testResourceByNumericKey()
    {
        $random = rand(0, count($this->expected) - 1);
        $expected = $this->expected[$random];

        $actual = $this->cluster->resourceByNumeric($random);
        $this->assertEquals($expected, $actual->cResourceIdentifier()->toString());
    }

    public function testRandom()
    {
        $actual = $this->cluster->random();
        $this->assertTrue(in_array($actual->cResourceIdentifier()->toString(), $this->expected));
    }

    public function testResourceByNumericKeyNotFound()
    {
        $this->expectException(ResourceNotFound::class);
        $this->cluster->resourceByNumeric($this->cluster->count());
    }

    public function testCount()
    {
        $this->assertEquals(count($this->expected), $this->cluster->count());
    }
}
