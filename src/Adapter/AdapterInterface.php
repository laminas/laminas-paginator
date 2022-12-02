<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use Countable;

/**
 * Interface for pagination adapters.
 *
 * @template-covariant TKey of int
 * @template-covariant TValue
 */
interface AdapterInterface extends Countable
{
    /**
     * Returns a collection of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return iterable<TKey, TValue>
     */
    public function getItems($offset, $itemCountPerPage);
}
