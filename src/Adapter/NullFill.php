<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use ReturnTypeWillChange;

use function array_fill;
use function min;

/**
 * @implements AdapterInterface<int, null>
 */
class NullFill implements AdapterInterface
{
    /**
     * @param int $count Total item count (Optional)
     */
    public function __construct(protected $count = 0)
    {
    }

    /**
     * Returns an array of items for a page.
     *
     * @inheritDoc
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $count = $this->count();
        if ($offset >= $count) {
            return [];
        }

        $remainItemCount  = $count - $offset;
        $currentItemCount = min($remainItemCount, $itemCountPerPage);

        return array_fill(0, $currentItemCount, null);
    }

    /**
     * Returns the total number of rows in the array.
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }
}
