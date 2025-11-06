<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayIterator;
use Iterator;
use OuterIterator;
use Traversable;

use function count;
use function is_int;
use function iterator_to_array;

/**
 * Class allowing for the continuous iteration of a Laminas\Paginator\Paginator instance.
 * Useful for representing remote paginated data sources as a single Iterator
 *
 * @template TKey of array-key
 * @template TValue
 * @implements OuterIterator<TKey, TValue>
 */
final class PaginatorIterator implements OuterIterator
{
    private bool $valid = true;

    /**
     * @param Paginator<TKey, TValue> $paginator Internal Paginator for iteration
     */
    public function __construct(
        private readonly Paginator $paginator
    ) {
    }

    /**
     * Return the current element
     *
     * @link https://php.net/manual/iterator.current.php
     *
     * @return TValue
     */
    public function current(): mixed
    {
        return $this->getInnerIterator()->current();
    }

    /**
     * Move forward to next element
     *
     * @link https://php.net/manual/iterator.next.php
     */
    public function next(): void
    {
        $innerIterator = $this->getInnerIterator();
        $innerIterator->next();

        if ($innerIterator->valid()) {
            return;
        }

        $page     = $this->paginator->getCurrentPageNumber();
        $nextPage = $page + 1;
        $this->paginator->setCurrentPageNumber($nextPage);

        $page = $this->paginator->getCurrentPageNumber();
        if ($page !== $nextPage) {
            $this->valid = false;
        }
    }

    /**
     * Return the key of the current element
     *
     * @link https://php.net/manual/iterator.key.php
     *
     * @return TKey
     */
    public function key(): int|string
    {
        $innerKey = $this->getInnerIterator()->key();
        if (is_int($innerKey)) {
            $innerKey++;
        }

        /** @psalm-var TKey $innerKey */
        $absoluteNumber = $this->paginator->getAbsoluteItemNumber(
            $innerKey,
            $this->paginator->getCurrentPageNumber()
        );
        if (is_int($absoluteNumber)) {
            $absoluteNumber--;
        }

        /** @psalm-var TKey $absoluteNumber */
        return $absoluteNumber;
    }

    /**
     * Checks if current position is valid
     *
     * @link https://php.net/manual/iterator.valid.php
     */
    public function valid(): bool
    {
        if (count($this->paginator) < 1) {
            $this->valid = false;
        }

        return $this->valid;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link https://php.net/manual/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->paginator->setCurrentPageNumber(1);
        $this->valid = true;
    }

    /**
     * Returns the inner iterator for the current entry.
     *
     * @link https://php.net/manual/outeriterator.getinneriterator.php
     *
     * @return Iterator<TKey, TValue> The inner iterator for the current entry.
     */
    public function getInnerIterator(): Iterator
    {
        $items = $this->paginator->getCurrentItems();
        if ($items instanceof Iterator) {
            /** @psalm-var Iterator<TKey, TValue> */
            return $items;
        }

        return $items instanceof Traversable
            ? new ArrayIterator(iterator_to_array($items))
            : new ArrayIterator($items);
    }
}
