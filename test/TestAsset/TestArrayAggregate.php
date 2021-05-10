<?php

namespace LaminasTest\Paginator\TestAsset;

use Laminas\Paginator;
use Laminas\Paginator\Adapter;

class TestArrayAggregate implements Paginator\AdapterAggregateInterface
{
    /**
     * @return Adapter\ArrayAdapter
     */
    public function getPaginatorAdapter()
    {
        return new Adapter\ArrayAdapter([1, 2, 3, 4]);
    }
}
