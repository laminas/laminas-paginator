<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

use ArrayObject;
use Laminas\Paginator\Adapter\AdapterInterface;
use ReturnTypeWillChange;

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
    #[ReturnTypeWillChange]
    public function count()
    {
        return 10;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $pageNumber
     * @param int $itemCountPerPage
     * @return iterable
     * @psalm-return ArrayObject<int, int>
     */
    public function getItems($pageNumber, $itemCountPerPage)
    {
        return new ArrayObject(range(1, 10));
    }
}
