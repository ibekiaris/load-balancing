<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing\Test;

use IBekiaris\LoadBalancing\Cluster;
use IBekiaris\LoadBalancing\FakeComputingResource;
use IBekiaris\LoadBalancing\ResourceId;
use IBekiaris\LoadBalancing\WeightedRoundRobin;
use IBekiaris\LoadBalancing\WeightMap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;

final class WeightedRoundRobinTest extends TestCase
{
    /**
     * @var Cluster
     */
    private $cluster;
    private $expected = [];

    public function setUp()
    {
        $weightMap = [
            '70973d9a-95ae-11e9-8398-91f0847efb5d' => 3,
            '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 10,
            '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 2,
            '709911f6-95ae-11e9-bf02-95b68eb31d60' => 4
        ];

        $this->expected = array_keys($weightMap);

        $expected = array_map(function (string $resourceId) {
            return new FakeComputingResource(ResourceId::fromString($resourceId));
        }, $this->expected);

        $this->cluster = new Cluster($expected, new WeightMap($weightMap));
    }

    /**
     * @dataProvider iterations
     */
    public function testAlgorithm(int $iterations, array $expected)
    {
        $roundRobin = new WeightedRoundRobin(new ArrayCache());

        $actual = [
            '70973d9a-95ae-11e9-8398-91f0847efb5d' => 0,
            '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 0,
            '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 0,
            '709911f6-95ae-11e9-bf02-95b68eb31d60' => 0
        ];

        for ($i = 0; $i < $iterations; $i++) {
            $resource = $roundRobin->pick($this->cluster);
            $resourceId = $resource->cResourceIdentifier()->toString();
            $actual[$resourceId] += 1;
        }

        $this->assertSameSize($expected, $actual);

        foreach ($expected as $resourceId => $scheduledVolume) {
            $this->assertEquals($scheduledVolume, $actual[$resourceId]);
        }
    }

    public function iterations(): array
    {
        return [
            [
                19,
                [
                    '70973d9a-95ae-11e9-8398-91f0847efb5d' => 3,
                    '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 10,
                    '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 2,
                    '709911f6-95ae-11e9-bf02-95b68eb31d60' => 4
                ],
            ],
            [
                38,
                [
                    '70973d9a-95ae-11e9-8398-91f0847efb5d' => 6,
                    '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 20,
                    '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 4,
                    '709911f6-95ae-11e9-bf02-95b68eb31d60' => 8
                ]
            ],
            [
                100,
                [
                    '70973d9a-95ae-11e9-8398-91f0847efb5d' => 15,
                    '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 55,
                    '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 10,
                    '709911f6-95ae-11e9-bf02-95b68eb31d60' => 20
                ]
            ],
            [
                632,
                [
                    '70973d9a-95ae-11e9-8398-91f0847efb5d' => 99,
                    '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 335,
                    '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 66,
                    '709911f6-95ae-11e9-bf02-95b68eb31d60' => 132
                ]
            ],
            [
                5,
                [
                    '70973d9a-95ae-11e9-8398-91f0847efb5d' => 0,
                    '7098bbde-95ae-11e9-aba6-2d9008b0e4b6' => 5,
                    '7098e9ce-95ae-11e9-b7a4-5d1a4da4e2b8' => 0,
                    '709911f6-95ae-11e9-bf02-95b68eb31d60' => 0
                ]
            ]
        ];
    }
}
