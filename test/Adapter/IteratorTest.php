<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter;

use ArrayIterator;
use Laminas\Paginator\Adapter\Exception\InvalidArgumentException;
use Laminas\Paginator\Adapter\Iterator;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\SerializableLimitIterator;
use LimitIterator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Traversable;

use function iterator_to_array;
use function range;
use function serialize;
use function unserialize;

final class IteratorTest extends TestCase
{
    /** @var Iterator<int, int> */
    private Iterator $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        /** @psalm-var Iterator<int, int> $iterator */
        $iterator = new Iterator(new ArrayIterator(range(1, 101)));

        $this->adapter = $iterator;
    }

    public function testGetsItemsAtOffsetZero(): void
    {
        $actual = $this->adapter->getItems(0, 10);
        $this->assertInstanceOf(SerializableLimitIterator::class, $actual);

        $i = 1;
        foreach ($actual as $item) {
            $this->assertEquals($i, $item);
            $i++;
        }
    }

    public function testGetsItemsAtOffsetTen(): void
    {
        $actual = $this->adapter->getItems(10, 10);
        $this->assertInstanceOf(SerializableLimitIterator::class, $actual);

        $i = 11;
        foreach ($actual as $item) {
            $this->assertEquals($i, $item);
            $i++;
        }
    }

    public function testReturnsCorrectCount(): void
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    public function testThrowsExceptionIfNotCountable(): void
    {
        $iterator = new LimitIterator(new ArrayIterator(range(1, 101)));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Iterator must implement Countable');
        new Iterator($iterator);
    }

    #[Group('Laminas-4151')]
    public function testDoesNotThrowOutOfBoundsExceptionIfIteratorIsEmpty(): void
    {
        $paginator = new Paginator(new Iterator(new ArrayIterator([])));
        $items     = $paginator->getCurrentItems();

        self::assertInstanceOf(Traversable::class, $items);
        $items = iterator_to_array($items);
        self::assertSame([], $items);
    }

    #[Group('Laminas-8084')]
    public function testGetItemsSerializable(): void
    {
        $items = $this->adapter->getItems(0, 1);
        self::assertInstanceOf(SerializableLimitIterator::class, $items);

        $innerIterator = $items->getInnerIterator();
        $items         = unserialize(serialize($items));
        self::assertInstanceOf(SerializableLimitIterator::class, $items);
        $this->assertEquals(
            $items->getInnerIterator(),
            $innerIterator,
            'getItems has to be serializable to use caching',
        );
    }

    #[Group('Laminas-4151')]
    public function testEmptySet(): void
    {
        $adapter = new Iterator(new ArrayIterator([]));
        $actual  = $adapter->getItems(0, 10);
        self::assertInstanceOf(SerializableLimitIterator::class, $actual);
        $this->assertSame([], iterator_to_array($actual));
    }

    public function testBasicBehaviourWithStringKeys(): void
    {
        $adapter = new Iterator(new ArrayIterator([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
        ]));

        self::assertCount(3, $adapter);

        self::assertSame(
            ['a' => 'a'],
            iterator_to_array($adapter->getItems(0, 1)),
        );

        self::assertSame(
            ['b' => 'b'],
            iterator_to_array($adapter->getItems(1, 1)),
        );

        self::assertSame(
            ['c' => 'c'],
            iterator_to_array($adapter->getItems(2, 1)),
        );
    }
}
