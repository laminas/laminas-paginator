<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

interface ScrollingStyleInterface
{
    /**
     * Returns an array of "local" pages given a page number and range.
     *
     * @param  int $pageRange (Optional) Page range
     * @return array<int, int>
     */
    public function getPages(Paginator $paginator, $pageRange = null);
}
