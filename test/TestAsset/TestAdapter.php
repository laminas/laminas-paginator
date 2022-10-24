<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

use ArrayObject;
use Laminas\Paginator\Adapter\AdapterInterface;
use ReturnTypeWillChange;

use function range;

class TestAdapter implements AdapterInterface
{
    public function __construct(public mixed $property = null)
    {
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
