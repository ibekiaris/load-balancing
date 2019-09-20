<?php declare(strict_types=1);

namespace IBekiaris\LoadBalancing;

final class ResourceId
{
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new static($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
