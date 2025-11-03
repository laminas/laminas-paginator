<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

use Laminas\Paginator;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;

/**
 * @implements Paginator\AdapterAggregateInterface<int, int>
 */
final class TestArrayAggregate implements Paginator\AdapterAggregateInterface
{
    /** @var array<int, int> */
    private array $items = [1, 2, 3, 4];

    /**
     * @return AdapterInterface<int, int>
     */
    public function getPaginatorAdapter(): AdapterInterface
    {
        return new ArrayAdapter($this->items);
    }
}
