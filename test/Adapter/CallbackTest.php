<?php

declare(strict_types=1);

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
        $itemsCallback =  * @return array
        $itemsCallback =  * @psalm-return array<empty, empty>
                          */
        static fn(): array => [];
        $countCallback = static fn(): int => 0;
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame([], $adapter->getItems(1, 1));
        $this->assertSame(0, $adapter->count());
    }

    public function testShouldAcceptAnyCallableOnConstructor(): void
    {
        $itemsCallback = /**
        $itemsCallback =  * @return int[]
        $itemsCallback =  * @psalm-return non-empty-list<int>
                          */
        static fn(): array => range(1, 10);
        $countCallback = 'rand';
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame(range(1, 10), $adapter->getItems(1, 1));
        $this->assertIsInt($adapter->count());
    }

    public function testMustRunItemCallbackToGetItems(): void
    {
        $data          = range(1, 10);
        $itemsCallback = /**
        $itemsCallback =  * @return int[]
        $itemsCallback =  * @psalm-return non-empty-list<int>
                          */
        static fn(): array => $data;
        $countCallback = static function (): int {
            return 10;
        };
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame($data, $adapter->getItems(0, 10));
    }

    public function testMustPassArgumentsToGetItemCallback(): void
    {
        $data          = [0, 1, 2, 3];
        $itemsCallback = /**
        $itemsCallback =  * @return (float|int|string)[]
        $itemsCallback =  * @psalm-return non-empty-list<float|int|string>
                          */
        static fn(int $offset, int $itemCountPerPage): array => range($offset, $itemCountPerPage);
        $countCallback = static function (): int {
            return 4;
        };
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame($data, $adapter->getItems(0, 3));
    }

    /**
     * @phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName
     */
    public function testMustRunCountCallbackToCount(): void
    {
        $count         = 1988;
        $itemsCallback = static function (int $_a, int $_b): array {
            return [];
        };
        $countCallback = static fn(): int => $count;
        $adapter       = new Callback($itemsCallback, $countCallback);

        $this->assertSame($count, $adapter->count());
    }
}
