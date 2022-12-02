<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

/**
 * A scrolling style that returns every page in the collection.
 * Useful when it is necessary to make every page available at
 * once--for example, when using a drop-down menu pagination control.
 */
class All implements ScrollingStyleInterface
{
    /**
     * Returns an array of all pages given a page number and range.
     *
     * @param  int $pageRange Unused
     * @return array<int, int>
     */
    public function getPages(Paginator $paginator, $pageRange = null)
    {
        return $paginator->getPagesInRange(1, $paginator->count());
    }
}
