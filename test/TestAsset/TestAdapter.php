<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

class TestAdapter implements \Laminas\Paginator\Adapter\AdapterInterface
{
    /**
     * @var mixed
     */
    public $property;

    public function __construct($property = null)
    {
        $this->property = $property;
    }

    public function count()
    {
        return 10;
    }

    public function getItems($pageNumber, $itemCountPerPage)
    {
        return new \ArrayObject(range(1, 10));
    }
}
