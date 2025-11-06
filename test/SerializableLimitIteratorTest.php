<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayIterator;
use Laminas\Paginator\SerializableLimitIterator;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;
use function serialize;
use function unserialize;

final class SerializableLimitIteratorTest extends TestCase
{
    public function testOffsetExists(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            0,
            5,
        );

        self::assertTrue($iterator->offsetExists(0));
        self::assertTrue($iterator->offsetExists(1));
        self::assertTrue($iterator->offsetExists(2));
        self::assertFalse($iterator->offsetExists(3));
        self::assertFalse($iterator->offsetExists(100));
    }

    public function testOffsetGet(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            0,
            5,
        );

        self::assertSame(1, $iterator->offsetGet(0));
        self::assertSame(2, $iterator->offsetGet(1));
        self::assertSame(3, $iterator->offsetGet(2));
    }

    public function testOutOfBoundsExceptionSeekingBeyondSize(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            0,
            5,
        );

        $this->expectException(OutOfBoundsException::class);
        $iterator->offsetGet(3);
    }

    public function testOutOfBoundsExceptionSeekingBeyondCount(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3, 4, 5, 6]),
            0,
            5,
        );

        $this->expectException(OutOfBoundsException::class);
        $iterator->offsetGet(5);
    }

    public function testSerializeRoundTrip(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            0,
            5,
        );

        $clone = unserialize(serialize($iterator));
        self::assertInstanceOf(SerializableLimitIterator::class, $clone);

        self::assertSame([1, 2, 3], iterator_to_array($iterator));
        self::assertSame([1, 2, 3], iterator_to_array($clone));
    }

    public function testSerializeRoundTripPreservesPosition(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            0,
            5,
        );

        $iterator->next();

        self::assertSame(2, $iterator->current());

        $clone = unserialize(serialize($iterator));
        self::assertInstanceOf(SerializableLimitIterator::class, $clone);

        self::assertSame(2, $clone->current());
    }

    public function testSerializeRoundTripPreservesOffset(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            1,
            5,
        );

        $clone = unserialize(serialize($iterator));
        self::assertInstanceOf(SerializableLimitIterator::class, $clone);

        self::assertSame([1 => 2, 2 => 3], iterator_to_array($clone));
    }

    public function testSerializeRoundTripPreservesCount(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3, 4, 5, 6]),
            1,
            2,
        );

        $clone = unserialize(serialize($iterator));
        self::assertInstanceOf(SerializableLimitIterator::class, $clone);

        self::assertSame([1 => 2, 2 => 3], iterator_to_array($clone));
    }

    public function testSerializableInterfaceMethods(): void
    {
        $iterator = new SerializableLimitIterator(
            new ArrayIterator([1, 2, 3]),
            0,
            5,
        );

        $serialized = serialize($iterator->__serialize());

        self::assertSame(
            $serialized,
            $iterator->serialize(),
        );
    }
}
