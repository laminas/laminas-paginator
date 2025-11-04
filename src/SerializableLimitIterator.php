<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayAccess;
use Iterator;
use LimitIterator;
use OutOfBoundsException;
use Serializable;

use function assert;
use function is_int;
use function serialize;
use function unserialize;

/**
 * @psalm-type SerialisedShape = array{it: Iterator, offset: int, count: int, pos: int}
 * @template TKey of int
 * @template TValue
 * @extends LimitIterator<TKey, TValue, Iterator<TKey, TValue>>
 * @implements ArrayAccess<TKey, TValue>
 */
final class SerializableLimitIterator extends LimitIterator implements Serializable, ArrayAccess
{
    /**
     * Offset to first element
     */
    private int $offset;

    /**
     * Maximum number of elements to show or -1 for all
     */
    private int $count;

    /**
     * @see LimitIterator::__construct
     *
     * @param Iterator<TKey, TValue> $it Iterator to limit (must be serializable by un-/serialize)
     * @param int $offset Offset to first element
     * @param int $count Maximum number of elements to show or -1 for all
     */
    public function __construct(Iterator $it, int $offset = 0, int $count = -1)
    {
        $this->offset = $offset;
        $this->count  = $count;
        parent::__construct($it, $offset, $count);
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    /** @return SerialisedShape */
    public function __serialize(): array
    {
        $iterator = $this->getInnerIterator();
        assert($iterator !== null);

        return [
            'it'     => $iterator,
            'offset' => $this->offset,
            'count'  => $this->count,
            'pos'    => $this->getPosition(),
        ];
    }

    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    /** @param SerialisedShape $data */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['it'], $data['offset'], $data['count']);
        $this->seek($data['pos'] + $data['offset']);
    }

    /**
     * Returns value of the Iterator
     *
     * @param TKey $offset
     * @return TValue|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        $currentOffset = $this->key() ?? 0;
        assert(is_int($currentOffset));
        $this->seek($offset);
        $current = $this->current();
        $this->seek($currentOffset);

        return $current;
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param TKey $offset
     * @param TValue $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    /**
     * Determine if a value of Iterator is set and is not NULL
     *
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        if ($offset > 0 && $offset < $this->count) {
            $currentOffset = $this->key() ?? 0;
            assert(is_int($currentOffset));
            try {
                $this->seek($offset);
                $current = $this->current();
                $this->seek($currentOffset);
                return null !== $current;
            } catch (OutOfBoundsException) {
                // reset position in case of exception is assigned null
                $this->seek($currentOffset);
                return false;
            }
        }
        return false;
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param TKey $offset
     */
    public function offsetUnset(mixed $offset): void
    {
    }
}
