<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use Countable;
use Laminas\Paginator\SerializableLimitIterator;
use ReturnTypeWillChange;

use function count;

/**
 * @template TKey of int
 * @template TValue
 * @implements AdapterInterface<TKey, TValue>
 */
class Iterator implements AdapterInterface
{
    /**
     * Iterator which implements Countable
     *
     * @var \Iterator<TKey, TValue>&Countable
     */
    protected $iterator;

    /**
     * Item count
     *
     * @var int
     */
    protected $count;

    /**
     * @param  \Iterator<TKey, TValue> $iterator Iterator to paginate
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(\Iterator $iterator)
    {
        if (! $iterator instanceof Countable) {
            throw new Exception\InvalidArgumentException('Iterator must implement Countable');
        }

        $this->iterator = $iterator;
        $this->count    = count($iterator);
    }

    /**
     * Returns an iterator of items for a page, or an empty array.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return iterable<TKey, TValue>
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->count === 0) {
            return [];
        }

        return new SerializableLimitIterator($this->iterator, $offset, $itemCountPerPage);
    }

    /**
     * Returns the total number of rows in the collection.
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }
}
