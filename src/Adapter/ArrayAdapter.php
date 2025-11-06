<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use function array_slice;
use function count;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements AdapterInterface<TKey, TValue>
 */
final readonly class ArrayAdapter implements AdapterInterface
{
    /** Item count */
    private int $count;

    /**
     * @param array<TKey, TValue> $array ArrayAdapter to paginate
     */
    public function __construct(private array $array = [])
    {
        $this->count = count($array);
    }

    public function getItems(int $offset, int $itemCountPerPage): iterable
    {
        return array_slice($this->array, $offset, $itemCountPerPage);
    }

    public function count(): int
    {
        return $this->count;
    }
}
