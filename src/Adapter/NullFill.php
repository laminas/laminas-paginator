<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use function array_fill;
use function min;

/**
 * @implements AdapterInterface<int, null>
 */
final readonly class NullFill implements AdapterInterface
{
    /**
     * @param int $count Total item count (Optional)
     */
    public function __construct(private int $count = 0)
    {
    }

    /**
     * Returns an array of items for a page.
     *
     * @inheritDoc
     */
    public function getItems(int $offset, int $itemCountPerPage): iterable
    {
        $count = $this->count();
        if ($offset >= $count) {
            return [];
        }

        $remainItemCount  = $count - $offset;
        $currentItemCount = min($remainItemCount, $itemCountPerPage);

        return array_fill(0, $currentItemCount, null);
    }

    public function count(): int
    {
        return $this->count;
    }
}
