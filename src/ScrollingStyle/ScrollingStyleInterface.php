<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

interface ScrollingStyleInterface
{
    /**
     * Returns an array of "local" pages given a page number and range.
     *
     * @param int<1, max>|null $pageRange (Optional) Page range
     * @return non-empty-array<int, int>
     */
    public function getPages(Paginator $paginator, int|null $pageRange = null): array;
}
