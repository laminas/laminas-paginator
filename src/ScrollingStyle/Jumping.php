<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

/**
 * A scrolling style in which the cursor advances to the upper bound
 * of the page range, the page range "jumps" to the next section, and
 * the cursor moves back to the beginning of the range.
 */
class Jumping implements ScrollingStyleInterface
{
    /**
     * Returns an array of "local" pages given a page number and range.
     *
     * @param  int $pageRange Unused
     * @return array<int, int>
     */
    public function getPages(Paginator $paginator, $pageRange = null)
    {
        $pageRange  = $paginator->getPageRange();
        $pageNumber = $paginator->getCurrentPageNumber();

        $delta = $pageNumber % $pageRange;

        if ($delta === 0) {
            $delta = $pageRange;
        }

        $offset     = $pageNumber - $delta;
        $lowerBound = $offset + 1;
        $upperBound = $offset + $pageRange;

        return $paginator->getPagesInRange($lowerBound, $upperBound);
    }
}
