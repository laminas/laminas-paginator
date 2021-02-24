<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter\PreselectedPaginator;
use PHPUnit\Framework\TestCase;

class PreselectedAdapterTest extends TestCase
{
    public function testAdapterCanBeCreatedWithoutParameters(): void
    {
        $adapter = new PreselectedPaginator();

        self::assertSame(0, $adapter->count());
        self::assertSame([], $adapter->getItems(1, 1));
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testCountMethodShouldReturnSameGivenValue(int $count): void
    {
        $adapter = new PreselectedPaginator([], $count);

        self::assertSame($count, $adapter->count());
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGetItemsMethodShouldReturnAlwaysSameResult(
        int $offset,
        int $itemCountPerPage
    ): void {
        $items   = range(1, 100);
        $adapter = new PreselectedPaginator($items);

        self::assertSame(
            $items,
            $adapter->getItems($offset, $itemCountPerPage)
        );
    }

    public function parameterProvider(): array
    {
        return [
            [
                1,
                1,
            ],
            [
                10,
                10,
            ],
            [
                100,
                100,
            ],
        ];
    }
}
