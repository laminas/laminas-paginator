<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function array_values;
use function assert;
use function ceil;
use function class_exists;
use function count;
use function get_debug_type;
use function is_array;
use function is_countable;
use function is_numeric;
use function is_string;
use function iterator_count;
use function iterator_to_array;
use function json_encode;
use function max;
use function min;
use function sprintf;
use function strtolower;

use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
final class Paginator implements Countable, IteratorAggregate
{
    /**
     * Configuration file
     *
     * @var array{itemcountperpage: positive-int, pagerange: positive-int, ...<string, mixed>}|null
     */
    private static array|null $config = null;

    /**
     * Default scrolling style
     *
     * @var string
     */
    protected static $defaultScrollingStyle = 'Sliding';

    /**
     * Default item count per page
     *
     * @var positive-int
     */
    protected static $defaultItemCountPerPage = 10;

    /**
     * Scrolling style plugin manager
     *
     * @var ScrollingStylePluginManager
     */
    protected static $scrollingStyles;

    /** @var AdapterInterface<TKey, TValue> */
    private AdapterInterface $adapter;

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
     * Number of items per page
     *
     * @var positive-int
     */
    private int $itemCountPerPage;

    /**
     * Number of pages
     *
     * @var int<0, max>|null
     */
    private int|null $pageCount = null;

    /**
     * Number of local pages (i.e., the number of discrete page numbers
     * that will be displayed, including the current page number)
     *
     * @var positive-int
     */
    private int $pageRange;

    /**
     * Set a global config
     *
     * @param array|Traversable $config
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public static function setGlobalConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        static::$config = $config;

        if (
            isset($config['scrolling_style_plugins'])
            && null !== ($adapters = $config['scrolling_style_plugins'])
        ) {
            static::setScrollingStylePluginManager($adapters);
        }

        $scrollingStyle = $config['scrolling_style'] ?? null;

        if ($scrollingStyle !== null) {
            static::setDefaultScrollingStyle($scrollingStyle);
        }
    }

    /**
     * Returns the default scrolling style.
     *
     * @return  string
     */
    public static function getDefaultScrollingStyle()
    {
        return static::$defaultScrollingStyle;
    }

    /**
     * Get the default item count per page
     *
     * @return positive-int
     */
    public static function getDefaultItemCountPerPage(): int
    {
        return self::$defaultItemCountPerPage;
    }

    /**
     * Set the default item count per page
     *
     * @param positive-int $count
     */
    public static function setDefaultItemCountPerPage(int $count): void
    {
        self::$defaultItemCountPerPage = $count;
    }

    /**
     * Sets the default scrolling style.
     *
     * @param string $scrollingStyle
     * @return void
     */
    public static function setDefaultScrollingStyle($scrollingStyle = 'Sliding')
    {
        static::$defaultScrollingStyle = $scrollingStyle;
    }

    /**
     * @param string|ScrollingStylePluginManager $scrollingAdapters
     * @return void
     */
    public static function setScrollingStylePluginManager($scrollingAdapters)
    {
        if (is_string($scrollingAdapters)) {
            if (! class_exists($scrollingAdapters)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate scrolling style plugin manager with class "%s"; class not found',
                    $scrollingAdapters
                ));
            }
            $scrollingAdapters = new $scrollingAdapters(new ServiceManager());
        }
        if (! $scrollingAdapters instanceof ScrollingStylePluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Pagination scrolling-style manager must extend ScrollingStylePluginManager; received "%s"',
                get_debug_type($scrollingAdapters)
            ));
        }
        static::$scrollingStyles = $scrollingAdapters;
    }

    /**
     * Returns the scrolling style manager. If it doesn't exist it's
     * created.
     *
     * @return ScrollingStylePluginManager
     */
    public static function getScrollingStylePluginManager()
    {
        if (static::$scrollingStyles === null) {
            static::$scrollingStyles = new ScrollingStylePluginManager(new ServiceManager());
        }

        return static::$scrollingStyles;
    }

    /**
     * @param AdapterInterface<TKey, TValue>|AdapterAggregateInterface<TKey, TValue> $adapter
     * @param positive-int|null $itemCountPerPage
     * @param positive-int|null $pageRange
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        AdapterInterface|AdapterAggregateInterface $adapter,
        int|null $itemCountPerPage = null,
        int|null $pageRange = null,
    ) {
        if ($adapter instanceof AdapterAggregateInterface) {
            $adapter = $adapter->getPaginatorAdapter();
        }

        $this->adapter = $adapter;

        $this->itemCountPerPage = $itemCountPerPage ?? self::getDefaultItemCountPerPage();
        $this->pageRange        = $pageRange ?? self::$config['pagerange'] ?? 10;

        $config = self::$config;

        if (is_array($config) && $config !== []) {
            $setupMethods = ['ItemCountPerPage', 'PageRange'];

            foreach ($setupMethods as $setupMethod) {
                $key   = strtolower($setupMethod);
                $value = $config[$key] ?? null;

                if ($value !== null) {
                    $setupMethod = 'set' . $setupMethod;
                    $this->$setupMethod($value);
                }
            }
        }
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
    public function getPages(string|null $scrollingStyle = null): Pages
    {
        return $this->createPages($scrollingStyle);
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
     * Returns the items of the current page as JSON.
     */
    public function toJson(): string
    {
        $currentItems  = $this->getCurrentItems();
        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

        return json_encode($currentItems, $encodeOptions);
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
    private function createPages(string|ScrollingStyleInterface|null $scrollingStyle = null): Pages
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
        $scrollingStyle   = $this->loadScrollingStyle($scrollingStyle);
        $pagesInRange     = $scrollingStyle->getPages($this);
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
     * Loads a scrolling style.
     *
     * @throws Exception\InvalidArgumentException
     */
    private function loadScrollingStyle(mixed $scrollingStyle = null): ScrollingStyleInterface
    {
        /** @psalm-var mixed $scrollingStyle */
        $scrollingStyle ??= self::$defaultScrollingStyle;

        if (is_string($scrollingStyle)) {
            /** @psalm-var ScrollingStyleInterface */
            return self::getScrollingStylePluginManager()->get($scrollingStyle);
        }

        if ($scrollingStyle instanceof ScrollingStyleInterface) {
            return $scrollingStyle;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Scrolling style must be a class name or an object implementing %s',
            ScrollingStyleInterface::class,
        ));
    }
}
