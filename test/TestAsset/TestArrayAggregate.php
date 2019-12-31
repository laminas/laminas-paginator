<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

use Laminas\Paginator;
use Laminas\Paginator\Adapter;

class TestArrayAggregate implements Paginator\AdapterAggregateInterface
{
    public function getPaginatorAdapter()
    {
        return new Adapter\ArrayAdapter([1, 2, 3, 4]);
    }
}
