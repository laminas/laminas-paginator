<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use Countable;

/**
 * Interface for pagination adapters.
 *
 * @template TKey of array-key
 * @template TValue
 */
interface AdapterInterface extends Countable
{
    /**
     * Returns a collection of items for a page.
     *
     * @param int<0, max> $offset Page offset
     * @param positive-int $itemCountPerPage Number of items per page
     * @return iterable<TKey, TValue>
     */
    public function getItems(int $offset, int $itemCountPerPage): iterable;
}
