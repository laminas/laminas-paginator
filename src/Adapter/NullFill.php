<?php

namespace Laminas\Paginator\Adapter;

use function array_fill;

class NullFill implements AdapterInterface
{
    /**
     * Item count
     *
     * @var int
     */
    protected $count;

    /**
     * @param int $count Total item count (Optional)
     */
    public function __construct($count = 0)
    {
        $this->count = $count;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $count = $this->count();
        if ($offset >= $count) {
            return [];
        }

        $remainItemCount  = $count - $offset;
        $currentItemCount = $remainItemCount > $itemCountPerPage ? $itemCountPerPage : $remainItemCount;

        return array_fill(0, $currentItemCount, null);
    }

    /**
     * Returns the total number of rows in the array.
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }
}
