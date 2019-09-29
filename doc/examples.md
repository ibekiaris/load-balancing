# Examples


## Forward equally traffic to several message brokers

Let's say that you have a daemon application that uses one instance of a message broker. 
There is a need to add an extra instance and you would like to equally forward traffic 
to both of those connection. 

Each connection can be considered to be a `ComputingResource`.

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

Those two connections are considered to be a `Cluster` of Computing Resources, in which each resource 
should have the same "weight" as others:

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

We need a `LoadBalancer` with a `Balancing Strategy` to forward "traffic" to `Cluster's` Computing Resources:

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