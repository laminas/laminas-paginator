<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleFactory;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Traversable;

use function array_values;
use function assert;
use function ceil;
use function count;
use function is_countable;
use function is_numeric;
use function is_string;
use function iterator_count;
use function iterator_to_array;
use function max;
use function min;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class Paginator implements Countable, IteratorAggregate
{
    /** @var AdapterInterface<TKey, TValue> */
    private readonly AdapterInterface $adapter;

    /**
     * Number of items in the current page
     *
     * @var int<0, max>|null
     */
    private int|null $currentItemCount = null;

    /**
     * Current page items
     *
     * @var iterable<TKey, TValue>|null
     */
    private iterable|null $currentItems = null;

    /**
     * Current page number (starting from 1)
     *
     * @var positive-int
     */
    private int $currentPageNumber = 1;

    /**
     * Number of pages
     *
     * @var int<0, max>|null
     */
    private int|null $pageCount = null;

    /**
     * @param AdapterInterface<TKey, TValue>|AdapterAggregateInterface<TKey, TValue> $adapter
     * @param positive-int $itemCountPerPage
     * @param positive-int $pageRange
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        AdapterInterface|AdapterAggregateInterface $adapter,
        private int $itemCountPerPage = 10,
        private int $pageRange = 10,
        private readonly ScrollingStyleInterface|string|null $scrollingStyle = null,
    ) {
        if ($adapter instanceof AdapterAggregateInterface) {
            $adapter = $adapter->getPaginatorAdapter();
        }

        $this->adapter = $adapter;
    }

    /**
     * Returns the number of pages.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        if ($this->pageCount === null) {
            $this->pageCount = $this->calculatePageCount();
        }

        return $this->pageCount;
    }

    /**
     * Returns the total number of items available.
     *
     * @return int<0, max>
     */
    public function getTotalItemCount(): int
    {
        return count($this->getAdapter());
    }

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param TKey|int $relativeItemNumber Relative item number
     * @return TKey
     */
    public function getAbsoluteItemNumber(int|string $relativeItemNumber, int|null $pageNumber = null): int|string
    {
        if (is_string($relativeItemNumber) && ! is_numeric($relativeItemNumber)) {
            return $relativeItemNumber;
        }

        $relativeItemNumber = $this->normalizeItemNumber($relativeItemNumber);
        $pageNumber         = $this->normalizePageNumber($pageNumber ?? $this->getCurrentPageNumber());

        /** @psalm-var TKey */
        return (($pageNumber - 1) * $this->getItemCountPerPage()) + $relativeItemNumber;
    }

    /**
     * Returns the adapter
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Returns the number of items for the current page.
     */
    public function getCurrentItemCount(): int
    {
        if ($this->currentItemCount === null) {
            $this->currentItemCount = $this->getItemCount($this->getCurrentItems());
        }

        return $this->currentItemCount;
    }

    /**
     * Returns the items for the current page.
     *
     * @return iterable<TKey, TValue>
     */
    public function getCurrentItems(): iterable
    {
        if ($this->currentItems === null) {
            $this->currentItems = $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->currentItems;
    }

    /**
     * Returns the current page number.
     *
     * @return int<1, max>
     */
    public function getCurrentPageNumber(): int
    {
        return $this->normalizePageNumber($this->currentPageNumber);
    }

    /**
     * Sets the current page number.
     *
     * @param int<1, max> $pageNumber
     * @return $this
     */
    public function setCurrentPageNumber(int $pageNumber): self
    {
        $this->currentPageNumber = $pageNumber;
        $this->currentItems      = null;
        $this->currentItemCount  = null;

        return $this;
    }

    /**
     * Returns an item from a page.  The current page is used if there's no
     * page specified.
     *
     * @param int $itemNumber Item number (1 to itemCountPerPage)
     * @throws Exception\InvalidArgumentException
     * @return TValue
     */
    public function getItem(int $itemNumber, int|null $pageNumber = null): mixed
    {
        if ($pageNumber === null) {
            $pageNumber = $this->getCurrentPageNumber();
        } elseif ($pageNumber < 0) {
            $pageNumber = ($this->count() + 1) + $pageNumber;
        }

        $page      = $this->getItemsByPage($pageNumber);
        $itemCount = $this->getItemCount($page);

        if ($itemCount === 0) {
            throw new Exception\InvalidArgumentException('Page ' . $pageNumber . ' does not exist');
        }

        if ($itemNumber <= 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            throw new Exception\InvalidArgumentException(
                "Page {$pageNumber} does not contain item number {$itemNumber}"
            );
        }

        $page = $page instanceof Traversable ? iterator_to_array($page, false) : array_values($page);

        return $page[$itemNumber - 1];
    }

    /**
     * Returns the number of items per page.
     *
     * @return int<1, max>
     */
    public function getItemCountPerPage(): int
    {
        return $this->itemCountPerPage;
    }

    /**
     * Sets the number of items per page.
     *
     * Setting a value of zero or less disables pagination
     *
     * @return $this
     */
    public function setItemCountPerPage(int $itemCountPerPage = -1): self
    {
        if ($itemCountPerPage < 1) {
            $this->itemCountPerPage = max(1, $this->getTotalItemCount());
        } else {
            $this->itemCountPerPage = $itemCountPerPage;
        }

        $this->pageCount        = $this->calculatePageCount();
        $this->currentItems     = null;
        $this->currentItemCount = null;

        return $this;
    }

    /**
     * Returns the number of items in a collection.
     *
     * @return int<0, max>
     */
    public function getItemCount(mixed $items): int
    {
        $itemCount = 0;

        if (is_countable($items)) {
            $itemCount = count($items);
        } elseif ($items instanceof Traversable) { // $items is something like LimitIterator
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     * Returns the items for a given page.
     *
     * @return iterable<TKey, TValue>
     */
    public function getItemsByPage(int $pageNumber): iterable
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->adapter->getItems($offset, $this->getItemCountPerPage());

        if (! $items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        return $items;
    }

    /**
     * Returns a foreach-compatible iterator.
     *
     * @throws Exception\RuntimeException
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        $items = $this->getCurrentItems();
        assert($items instanceof Iterator);

        /**
         * Forcing here because we lose inference by returning `iterable<k, v>` in all methods
         *
         * @psalm-var Traversable<TKey, TValue> $items
         */
        return $items;
    }

    /**
     * Returns the page range (see property declaration above).
     *
     * @return int<1, max>
     */
    public function getPageRange(): int
    {
        return $this->pageRange;
    }

    /**
     * Sets the page range (see property declaration above).
     *
     * @param int<1, max> $pageRange
     * @return $this
     */
    public function setPageRange(int $pageRange): self
    {
        $this->pageRange = $pageRange;

        return $this;
    }

    /**
     * Returns the page collection.
     */
    public function getPages(string|ScrollingStyleInterface|null $scrollingStyle = null): Pages
    {
        $style = $this->loadScrollingStyle($scrollingStyle ?? $this->scrollingStyle);

        return $this->createPages($style);
    }

    /**
     * Returns a subset of pages within a given range.
     *
     * @param  int $lowerBound Lower bound of the range
     * @param  int $upperBound Upper bound of the range
     * @return non-empty-array<int, int>
     */
    public function getPagesInRange(int $lowerBound, int $upperBound): array
    {
        $lowerBound = $this->normalizePageNumber($lowerBound);
        $upperBound = $this->normalizePageNumber($upperBound);

        $pages = [];

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = $pageNumber;
        }

        assert($pages !== []);

        return $pages;
    }

    /**
     * Brings the item number in range of the page.
     */
    public function normalizeItemNumber(int $itemNumber): int
    {
        if ($itemNumber < 1) {
            $itemNumber = 1;
        }

        if ($itemNumber > $this->getItemCountPerPage()) {
            $itemNumber = $this->getItemCountPerPage();
        }

        return $itemNumber;
    }

    /**
     * Brings the page number in range of the paginator.
     *
     * @return int<1, max>
     */
    public function normalizePageNumber(int $pageNumber): int
    {
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->count();

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * Calculates the page count.
     *
     * @return int<0, max>
     */
    private function calculatePageCount(): int
    {
        $count = (int) ceil($this->getAdapter()->count() / $this->getItemCountPerPage());
        assert($count >= 0);

        return $count;
    }

    /**
     * Creates the page collection.
     */
    private function createPages(ScrollingStyleInterface $scrollingStyle): Pages
    {
        $pageCount         = $this->count();
        $currentPageNumber = $this->getCurrentPageNumber();

        // Previous and next
        $previous = $next = null;
        if ($currentPageNumber - 1 > 0) {
            $previous = $currentPageNumber - 1;
            assert($previous >= 1);
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $next = $currentPageNumber + 1;
        }

        // Pages in range
        $pagesInRange     = $scrollingStyle->getPages($this, $this->pageRange);
        $firstPageInRange = min($pagesInRange);
        $lastPageInRange  = max($pagesInRange);
        assert($firstPageInRange >= 1);
        assert($lastPageInRange >= 1);

        // Item numbers
        $currentItemCount = $this->getCurrentItemCount();
        assert($currentItemCount >= 0);
        $totalItemCount  = $this->getTotalItemCount();
        $firstItemNumber = $totalItemCount
            ? (($currentPageNumber - 1) * $this->itemCountPerPage) + 1
            : 0;
        $lastItemNumber  = $totalItemCount
            ? $firstItemNumber + $currentItemCount - 1
            : 0;
        assert($lastItemNumber >= 0);

        return new Pages(
            $pageCount,
            $this->itemCountPerPage,
            1,
            $currentPageNumber,
            $pageCount === 0 ? 1 : $pageCount,
            $previous,
            $next,
            $pagesInRange,
            $firstPageInRange,
            $lastPageInRange,
            $currentItemCount,
            $totalItemCount,
            $firstItemNumber,
            $lastItemNumber,
        );
    }

    /**
     * Resolve the given scrolling style or return the default style
     */
    private function loadScrollingStyle(
        string|ScrollingStyleInterface|null $scrollingStyle = null,
    ): ScrollingStyleInterface {
        if ($scrollingStyle instanceof ScrollingStyleInterface) {
            return $scrollingStyle;
        }

        return ScrollingStyleFactory::fromString((string) $scrollingStyle, true);
    }
}
