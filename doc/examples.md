# Examples


## Forward equally traffic to several message brokers

Let's say that you have a daemon application that uses one instance of a message broker. There is a need to add
an extra instance and you would like to equally forward traffic to both of them. 
Each connection can be considered as a `ComputingResource`.

So, we have two `ComputingResources`:

```php
<?php
namespace DaemonApp;

use IBekiaris\LoadBalancing\ComputingResource;
use IBekiaris\LoadBalancing\ResourceId;

final class Connection1 implements ComputingResource {

    private $weight;

    public function cResourceIdentifier() : ResourceId
    {
        return ResourceId::fromString('Connection1');
    }

    public function weight() : int
    {
        return $this->weight;
    }

    public function withWeight(int $weight) : ComputingResource
    {
        $self = clone $this;
        $self->weight = $weight;
        return $self;
    }
}

final class Connection2 implements ComputingResource {

    private $weight;

    public function cResourceIdentifier() : ResourceId
    {
        return ResourceId::fromString('Connection2');
    }

    public function weight() : int
    {
        return $this->weight;
    }

    public function withWeight(int $weight) : ComputingResource
    {
        $self = clone $this;
        $self->weight = $weight;
        return $self;
    }
}
```
Those two connections are a `Cluster` of computing resources, in which each resource 
should have the same weight as others:

```php
<?php
namespace DaemonApp;

use IBekiaris\LoadBalancing\Cluster;
use IBekiaris\LoadBalancing\WeightMap;

$resources = [
    new Connection1(),
    new Connection2(),
];

$cluster = new Cluster($resources, WeightMap::equal($resources));
```

We need a `LoadBalancer` with a `balancing strategy` to forward "traffic" to `Cluster's` computing resources:

```php
<?php
namespace DaemonApp;

use IBekiaris\LoadBalancing\LoadBalancerWithStrategy;

final class MyLoadBalancer extends LoadBalancerWithStrategy {}
```

The final result could be something like the following:

```php
<?php
namespace DaemonApp;

use IBekiaris\LoadBalancing\LoadBalancer;
use IBekiaris\LoadBalancing\RoundRobin;
use Symfony\Component\Cache\Simple\ArrayCache;
use IBekiaris\LoadBalancing\Cluster;
use IBekiaris\LoadBalancing\WeightMap;

function loadBalancerFactory(): LoadBalancer {
    $roundRobinStrategy = new RoundRobin(new ArrayCache());
    $balancer = new MyLoadBalancer($roundRobinStrategy);
    return $balancer;
}

$resources = [
    new Connection1(),
    new Connection2(),
];

$cluster = new Cluster($resources, WeightMap::equal($resources));

$loadBalancer = loadBalancerFactory();
$selectedConnection = $loadBalancer->pick($cluster); // This can be a infinite loop in your daemon application

```