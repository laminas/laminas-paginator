<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

use function count;

/**
 * A Google-like scrolling style.  Incrementally expands the range to about
 * twice the given page range, then behaves like a slider.  See the example
 * link.
 *
 * @link https://www.google.com/search?q=Laminas+Framework
 */
final class Elastic implements ScrollingStyleInterface
{
    public function getPages(Paginator $paginator, int|null $pageRange = null): array
    {
        $pageRange  = $paginator->getPageRange();
        $pageNumber = $paginator->getCurrentPageNumber();

        $originalPageRange = $pageRange;
        $pageRange         = $pageRange * 2 - 1;

        if ($originalPageRange + $pageNumber - 1 < $pageRange) {
            $pageRange = $originalPageRange + $pageNumber - 1;
        } elseif ($originalPageRange + $pageNumber - 1 > $count = count($paginator)) {
            $pageRange = $originalPageRange + $count - $pageNumber;
        }

        $sliding = new Sliding();

        return $sliding->getPages($paginator, $pageRange);
    }
}
