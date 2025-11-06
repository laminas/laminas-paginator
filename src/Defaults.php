<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class Defaults
{
    /**
     * @param int<1, max> $itemCountPerPage
     * @param int<1, max> $pageRange
     */
    public function __construct(
        public int $itemCountPerPage,
        public int $pageRange,
        public ScrollingStyleInterface $scrollingStyle,
    ) {
    }
}
