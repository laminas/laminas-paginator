<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

use ArrayObject;
use Laminas\Paginator\Adapter\AdapterInterface;

use function range;

class TestAdapter implements AdapterInterface
{
    /** @var mixed */
    public $property;

    /**
     * @param mixed $property
     */
    public function __construct($property = null)
    {
        $this->property = $property;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return 10;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($pageNumber, $itemCountPerPage)
    {
        return new ArrayObject(range(1, 10));
    }
}
