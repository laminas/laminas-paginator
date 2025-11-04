<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Paginator;

/**
 * A scrolling style that returns every page in the collection.
 * Useful when it is necessary to make every page available at
 * once--for example, when using a drop-down menu pagination control.
 */
final class All implements ScrollingStyleInterface
{
    public function getPages(Paginator $paginator, int|null $pageRange = null): array
    {
        return $paginator->getPagesInRange(1, $paginator->count());
    }
}
