<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Laminas\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Laminas\Cache\Storage\Adapter\Memory;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\CachingAdapter;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;

use function range;
use function sleep;
use function sprintf;

final class CachingAdapterTest extends TestCase
{
    private CacheItemPoolDecorator $cache;
    private FrozenClock $clock;

    public function setUp(): void
    {
        parent::setUp();

        $this->clock = new FrozenClock(new DateTimeImmutable('2025-01-01 10:00:00', new DateTimeZone('UTC')));
        $this->cache = new CacheItemPoolDecorator(new Memory(), $this->clock);
    }

    public function testCountIsCorrect(): void
    {
        $arrayAdapter = new ArrayAdapter(range(1, 100));
        $adapter      = new CachingAdapter(
            $arrayAdapter,
            'foo',
            $this->cache,
            null,
        );

        self::assertSame($arrayAdapter->count(), $adapter->count());
    }

    public function testItemsWillBeCached(): void
    {
        $key     = sprintf('%s-%d-%d', 'foo', 0, 10);
        $adapter = new CachingAdapter(
            new ArrayAdapter(range(1, 100)),
            'foo',
            $this->cache,
            new DateInterval('PT1S'),
        );

        $item = $this->cache->getItem($key);
        self::assertFalse($item->isHit());

        $items = $adapter->getItems(0, 10);
        self::assertSame(range(1, 10), $items);

        $item = $this->cache->getItem($key);
        self::assertTrue($item->isHit());
        self::assertSame(range(1, 10), $item->get());
        self::assertSame(range(1, 10), $adapter->getItems(0, 10));
    }

    public function testItemsWillHaveTheExpectedTtl(): void
    {
        $key     = sprintf('%s-%d-%d', 'bar', 0, 10);
        $adapter = new CachingAdapter(
            new ArrayAdapter(range(1, 100)),
            'bar',
            $this->cache,
            new DateInterval('PT1S'),
        );

        $adapter->getItems(0, 10);

        // Freezing the clock does not work unfortunately…

        sleep(1);

        $item = $this->cache->getItem($key);
        self::assertFalse($item->isHit());
    }

    public function testInvalidResultsAreReturnedWithIdenticalCachePrefixes(): void
    {
        $adapter = new CachingAdapter(
            new ArrayAdapter(range(1, 100)),
            'baz',
            $this->cache,
            new DateInterval('PT10M'),
        );

        $adapter2 = new CachingAdapter(
            new ArrayAdapter(range(101, 200)),
            'baz',
            $this->cache,
            new DateInterval('PT10M'),
        );

        $items = $adapter->getItems(0, 10);
        self::assertSame(range(1, 10), $items);

        $cached = $adapter2->getItems(0, 10);

        self::assertSame(range(1, 10), $cached);
    }
}
