<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

use ArrayObject;
use Laminas\Paginator\Adapter\AdapterInterface;

use function range;

/**
 * @implements AdapterInterface<int, int>
 */
class TestAdapter implements AdapterInterface
{
    public function __construct(public mixed $property = null)
    {
    }

    public function count(): int
    {
        return 10;
    }

    /** @inheritDoc */
    public function getItems(int $offset, int $itemCountPerPage): iterable
    {
        return new ArrayObject(range(1, 10));
    }
}
