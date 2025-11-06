<?php

declare(strict_types=1);

namespace Laminas\Paginator;

/** @psalm-api None of these properties are un-used */
final readonly class Pages
{
    /**
     * @param int<0, max> $pageCount
     * @param int<1, max> $itemCountPerPage
     * @param int<1, max> $first
     * @param int<1, max> $current
     * @param int<1, max> $last
     * @param int<1, max>|null $previous
     * @param int<2, max>|null $next
     * @param array<int, int> $pagesInRange
     * @param int<1, max> $firstPageInRange
     * @param int<1, max> $lastPageInRange
     * @param int<0, max> $currentItemCount
     * @param int<0, max> $totalItemCount
     * @param int<0, max> $firstItemNumber
     * @param int<0, max> $lastItemNumber
     */
    public function __construct(
        public int $pageCount,
        public int $itemCountPerPage,
        public int $first,
        public int $current,
        public int $last,
        public int|null $previous,
        public int|null $next,
        public array $pagesInRange,
        public int $firstPageInRange,
        public int $lastPageInRange,
        public int $currentItemCount,
        public int $totalItemCount,
        public int $firstItemNumber,
        public int $lastItemNumber,
    ) {
    }
}
