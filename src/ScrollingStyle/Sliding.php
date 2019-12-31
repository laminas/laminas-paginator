<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

/**
 * A Yahoo! Search-like scrolling style.  The cursor will advance to
 * the middle of the range, then remain there until the user reaches
 * the end of the page set, at which point it will continue on to
 * the end of the range and the last page in the set.
 *
 * @link       http://search.yahoo.com/search?p=Laminas+Framework
 */
class Sliding implements ScrollingStyleInterface
{
    /**
     * Returns an array of "local" pages given a page number and range.
     *
     * @param  Paginator $paginator
     * @param  int $pageRange (Optional) Page range
     * @return array
     */
    public function getPages(Paginator $paginator, $pageRange = null)
    {
        if ($pageRange === null) {
            $pageRange = $paginator->getPageRange();
        }

        $pageNumber = $paginator->getCurrentPageNumber();
        $pageCount  = count($paginator);

        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($pageNumber - $delta > $pageCount - $pageRange) {
            $lowerBound = $pageCount - $pageRange + 1;
            $upperBound = $pageCount;
        } else {
            if ($pageNumber - $delta < 0) {
                $delta = $pageNumber;
            }

            $offset     = $pageNumber - $delta;
            $lowerBound = $offset + 1;
            $upperBound = $offset + $pageRange;
        }

        return $paginator->getPagesInRange($lowerBound, $upperBound);
    }
}
