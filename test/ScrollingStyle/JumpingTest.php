<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\ScrollingStyle;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\Jumping;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function range;

final class JumpingTest extends TestCase
{
    /**
     * @return list<array{
     *     0: int<1, max>,
     *     1: int<1, max>,
     *     2: int<1, max>,
     *     3: array<int, int>,
     *     4: int|null,
     *     5: int|null
     * }>
     */
    public static function expectedRangeProvider(): array
    {
        return [
            [10, 5, 1, array_combine(range(1, 5), range(1, 5)), 2, null],
            [10, 5, 2, array_combine(range(1, 5), range(1, 5)), 3, 1],
            [10, 5, 5, array_combine(range(1, 5), range(1, 5)), 6, 4],
            [10, 5, 6, array_combine(range(6, 10), range(6, 10)), 7, 5],
            [10, 5, 9, array_combine(range(6, 10), range(6, 10)), 10, 8],
            [10, 5, 10, array_combine(range(6, 10), range(6, 10)), 11, 9],
            [10, 5, 11, array_combine(range(11, 15), range(11, 15)), 12, 10],
            [10, 5, 21, [21 => 21], null, 20],
        ];
    }

    /**
     * @param int<1, max> $pageSize
     * @param int<1, max> $range
     * @param int<1, max> $currentPage
     * @param array<int, int> $expectRange
     */
    #[DataProvider('expectedRangeProvider')]
    public function testExpectedRanges(int $pageSize, int $range, int $currentPage, array $expectRange): void
    {
        $pager = new Paginator(new ArrayAdapter(range(1, 201)));
        $pager->setItemCountPerPage($pageSize);
        $pager->setPageRange($range);
        $pager->setCurrentPageNumber($currentPage);

        self::assertSame($expectRange, $pager->getPages(new Jumping())->pagesInRange);
    }

    /**
     * @param int<1, max> $pageSize
     * @param int<1, max> $range
     * @param int<1, max> $currentPage
     * @param array<int, int> $expectRange
     * @psalm-suppress UnusedParam
     */
    #[DataProvider('expectedRangeProvider')]
    public function testExpectedNextAndPreviousPage(
        int $pageSize,
        int $range,
        int $currentPage,
        array $expectRange,
        int|null $expectNext,
        int|null $expectPrevious,
    ): void {
        $pager = new Paginator(new ArrayAdapter(range(1, 201)));
        $pager->setItemCountPerPage($pageSize);
        $pager->setPageRange($range);
        $pager->setCurrentPageNumber($currentPage);

        $pages = $pager->getPages(new Jumping());

        self::assertSame($expectNext, $pages->next);
        self::assertSame($expectPrevious, $pages->previous);
    }
}
