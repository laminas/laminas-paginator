<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter\Callback;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * @covers  Laminas\Paginator\Adapter\Callback<extended>
 */
class CallbackTest extends TestCase
{
    public function testMustDefineTwoCallbacksOnConstructor(): void
    {
        $itemsCallback = /**
         * @return array
         *
         * @psalm-return array<empty, empty>
         */
        function (): array {
            return [];
        };
        $countCallback = function (): int {
            return 0;
        };
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame([], $adapter->getItems(1, 1));
        $this->assertSame(0, $adapter->count());
    }

    public function testShouldAcceptAnyCallableOnConstructor(): void
    {
        $itemsCallback = /**
         * @return int[]
         *
         * @psalm-return non-empty-list<int>
         */
        function (): array {
            return range(1, 10);
        };
        $countCallback = 'rand';
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame(range(1, 10), $adapter->getItems(1, 1));
        $this->assertIsInt($adapter->count());
    }

    public function testMustRunItemCallbackToGetItems(): void
    {
        $data          = range(1, 10);
        $itemsCallback = /**
         * @return int[]
         *
         * @psalm-return non-empty-list<int>
         */
        function () use ($data): array {
            return $data;
        };
        $countCallback = function (): void {
        };
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame($data, $adapter->getItems(0, 10));
    }

    public function testMustPassArgumentsToGetItemCallback(): void
    {
        $data          = [0, 1, 2, 3];
        $itemsCallback = /**
         * @return (float|int|string)[]
         *
         * @psalm-return non-empty-list<float|int|string>
         */
        function ($offset, $itemCountPerPage): array {
            return range($offset, $itemCountPerPage);
        };
        $countCallback = function (): void {
        };
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame($data, $adapter->getItems(0, 3));
    }

    public function testMustRunCountCallbackToCount(): void
    {
        $count         = 1988;
        $itemsCallback = function (): void {
        };
        $countCallback = function () use ($count): int {
            return $count;
        };
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame($count, $adapter->count());
    }
}
