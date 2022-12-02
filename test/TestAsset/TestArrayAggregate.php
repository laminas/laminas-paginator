<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

use Laminas\Paginator;
use Laminas\Paginator\Adapter;

/**
 * @implements Paginator\AdapterAggregateInterface<int, int>
 */
class TestArrayAggregate implements Paginator\AdapterAggregateInterface
{
    /**
     * @return Adapter\ArrayAdapter<int, int>
     */
    public function getPaginatorAdapter()
    {
        return new Adapter\ArrayAdapter([1, 2, 3, 4]);
    }
}
