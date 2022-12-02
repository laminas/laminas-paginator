<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use OuterIterator;
use ReturnTypeWillChange;

use function assert;
use function count;
use function is_int;

/**
 * Class allowing for the continuous iteration of a Laminas\Paginator\Paginator instance.
 * Useful for representing remote paginated data sources as a single Iterator
 *
 * @template TKey of int
 * @template TValue
 * @implements OuterIterator<TKey, TValue>
 */
class PaginatorIterator implements OuterIterator
{
    /**
     * Value for valid method
     *
     * @var bool $valid
     */
    protected $valid = true;

    /**
     * @param Paginator<TKey, TValue> $paginator Internal Paginator for iteration
     */
    public function __construct(
        protected Paginator $paginator
    ) {
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return TValue Can return any type.
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->getInnerIterator()->current();
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     */
    #[ReturnTypeWillChange]
    public function next()
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
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return TKey|null scalar on success, or null on failure.
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        $innerKey = $this->getInnerIterator()->key();
        assert(is_int($innerKey));
        ++$innerKey; //Laminas\Paginator\Paginator normalizes 0 to 1
        assert(is_int($innerKey));

        $this->paginator->getCurrentPageNumber();
        return ($this->paginator->getAbsoluteItemNumber(
            $innerKey,
            $this->paginator->getCurrentPageNumber()
        )) - 1;
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    #[ReturnTypeWillChange]
    public function valid()
    {
        if (count($this->paginator) < 1) {
            $this->valid = false;
        }
        return $this->valid;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->paginator->setCurrentPageNumber(1);
        $this->valid = true;
    }

    /**
     * Returns the inner iterator for the current entry.
     *
     * @link http://php.net/manual/en/outeriterator.getinneriterator.php
     *
     * @return iterable<TKey, TValue> The inner iterator for the current entry.
     */
    #[ReturnTypeWillChange]
    public function getInnerIterator()
    {
        return $this->paginator->getCurrentItems();
    }
}
