<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayAccess;
use Iterator;
use LimitIterator;
use OutOfBoundsException;
use ReturnTypeWillChange;
use Serializable;

use function assert;
use function is_int;
use function serialize;
use function unserialize;

/**
 * @template TKey of int
 * @template TValue
 * @template-extends LimitIterator<TKey, TValue, Iterator<TKey, TValue>>
 * @implements ArrayAccess<TKey, TValue>
 */
class SerializableLimitIterator extends LimitIterator implements Serializable, ArrayAccess
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
     * Construct a Laminas\Paginator\SerializableLimitIterator
     *
     * @see LimitIterator::__construct
     *
     * @param Iterator<TKey, TValue> $it Iterator to limit (must be serializable by un-/serialize)
     * @param int $offset Offset to first element
     * @param int $count Maximum number of elements to show or -1 for all
     */
    public function __construct(Iterator $it, $offset = 0, $count = -1)
    {
        $this->offset = $offset;
        $this->count  = $count;
        parent::__construct($it, $offset, $count);
    }

    /**
     * @return string representation of the instance
     */
    public function serialize()
    {
        return serialize($this->__serialize);
    }

    public function __serialize(): array
    {
        return [
            'it'     => $this->getInnerIterator(),
            'offset' => $this->offset,
            'count'  => $this->count,
            'pos'    => $this->getPosition(),
        ];
    }

    /**
     * @param string $data representation of the instance
     * @return void
     */
    public function unserialize($data)
    {
        $this->__unserialize(unserialize($data));
    }

    public function __unserialize(array $data)
    {
        $this->__construct($data['it'], $data['offset'], $data['count']);
        $this->seek($data['pos'] + $data['offset']);
    }

    /**
     * Returns value of the Iterator
     *
     * @param int $offset
     * @return TValue|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
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
    #[ReturnTypeWillChange]
    public function offsetSet($offset, mixed $value)
    {
    }

    /**
     * Determine if a value of Iterator is set and is not NULL
     *
     * @param int $offset
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
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
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }
}
