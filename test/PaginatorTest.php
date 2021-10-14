<?php

namespace LaminasTest\Paginator;

use ArrayIterator;
use ArrayObject;
use DirectoryIterator;
use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory as CacheFactory;
use Laminas\Config;
use Laminas\Filter;
use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Exception;
use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\Paginator\SerializableLimitIterator;
use Laminas\View;
use Laminas\View\Exception\ExceptionInterface;
use Laminas\View\Helper;
use Laminas\View\Renderer\RendererInterface;
use LaminasTest\Paginator\TestAsset\ScrollingStylePluginManager;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;
use LimitIterator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use stdClass;

use function array_combine;
use function count;
use function in_array;
use function is_dir;
use function mkdir;
use function range;
use function rmdir;
use function rtrim;
use function sys_get_temp_dir;
use function unlink;

use const DIRECTORY_SEPARATOR;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Paginator<extended>
 */
class PaginatorTest extends TestCase
{
    /**
     * Paginator instance
     *
     * @var Paginator\Paginator
     */
    protected $paginator;

    /** @var array */
    protected $testCollection;

    /** @var StorageInterface */
    protected $cache;

    /** @var string */
    protected $cacheDir;

    /** @var array */
    protected $config;

    protected function setUp(): void
    {
        $this->testCollection = range(1, 101);
        $this->paginator      = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter($this->testCollection));

        $this->config = Config\Factory::fromFile(__DIR__ . '/_files/config.xml', true);

        $this->cache = CacheFactory::adapterFactory('memory', ['memory_limit' => 0]);
        Paginator\Paginator::setCache($this->cache);

        $this->_restorePaginatorDefaults();
    }

    protected function tearDown(): void
    {
        $this->testCollection = null;
        $this->paginator      = null;
    }

    // @codingStandardsIgnoreStart
    protected function _getTmpDir(): string
    {
        // @codingStandardsIgnoreEnd
        $tmpDir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'laminas_paginator';
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir);
        }
        $this->cacheDir = $tmpDir;

        return $tmpDir;
    }

    // @codingStandardsIgnoreStart
    protected function _rmDirRecursive(string $path): void
    {
        // @codingStandardsIgnoreEnd
        $dir = new DirectoryIterator($path);
        foreach ($dir as $file) {
            if (! $file->isDir()) {
                unlink($file->getPathname());
            } elseif (! in_array($file->getFilename(), ['.', '..'])) {
                $this->_rmDirRecursive($file->getPathname());
            }
        }
        unset($file, $dir); // required on windows to remove file handle
        if (! rmdir($path)) {
            throw new Exception\RuntimeException('Unable to remove temporary directory ' . $path
                                . '; perhaps it has a nested structure?');
        }
    }

    // @codingStandardsIgnoreStart
    protected function _restorePaginatorDefaults(): void
    {
        // @codingStandardsIgnoreEnd
        $this->paginator->setItemCountPerPage(10);
        $this->paginator->setCurrentPageNumber(1);
        $this->paginator->setPageRange(10);
        $this->paginator->setView();

        Paginator\Paginator::setDefaultScrollingStyle();
        Helper\PaginationControl::setDefaultViewPartial(null);

        Paginator\Paginator::setGlobalConfig($this->config->default);

        Paginator\Paginator::setScrollingStylePluginManager(new Paginator\ScrollingStylePluginManager(
            $this->getMockBuilder(ContainerInterface::class)->getMock()
        ));

        $this->paginator->setCacheEnabled(true);
    }

    public function testGetsAndSetsDefaultScrollingStyle(): void
    {
        $this->assertEquals(Paginator\Paginator::getDefaultScrollingStyle(), 'Sliding');
        Paginator\Paginator::setDefaultScrollingStyle('Scrolling');
        $this->assertEquals(Paginator\Paginator::getDefaultScrollingStyle(), 'Scrolling');
        Paginator\Paginator::setDefaultScrollingStyle('Sliding');
    }

    public function testHasCorrectCountAfterInit(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $this->assertEquals(11, $paginator->count());
    }

    public function testHasCorrectCountOfAllItemsAfterInit(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $this->assertEquals(101, $paginator->getTotalItemCount());
    }

    public function testRepetitiveCallOfCountResultsOfZero(): void
    {
        $count = 0;

        $paginator = $this->getMockBuilder(Paginator\Paginator::class)
            ->setConstructorArgs([new Adapter\ArrayAdapter([])])
            ->setMethods(['_calculatePageCount'])
            ->getMock();

        $paginator->expects($this->once())
            ->method('_calculatePageCount')
            ->willReturn($count);

        $this->assertEquals($count, $paginator->count());
        $this->assertEquals($count, $paginator->count());
    }

    public function testLoadsFromConfig(): void
    {
        Paginator\Paginator::setGlobalConfig($this->config->testing);
        $this->assertEquals('Scrolling', Paginator\Paginator::getDefaultScrollingStyle());

        $plugins = Paginator\Paginator::getScrollingStylePluginManager();
        $this->assertInstanceOf(ScrollingStylePluginManager::class, $plugins);

        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $this->assertEquals(3, $paginator->getItemCountPerPage());
        $this->assertEquals(7, $paginator->getPageRange());
    }

    public function testGetsPagesForPageOne(): void
    {
        $expected                   = new stdClass();
        $expected->pageCount        = 11;
        $expected->itemCountPerPage = 10;
        $expected->first            = 1;
        $expected->current          = 1;
        $expected->last             = 11;
        $expected->next             = 2;
        $expected->pagesInRange     = array_combine(range(1, 10), range(1, 10));
        $expected->firstPageInRange = 1;
        $expected->lastPageInRange  = 10;
        $expected->currentItemCount = 10;
        $expected->totalItemCount   = 101;
        $expected->firstItemNumber  = 1;
        $expected->lastItemNumber   = 10;

        $actual = $this->paginator->getPages();

        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesForPageTwo(): void
    {
        $expected                   = new stdClass();
        $expected->pageCount        = 11;
        $expected->itemCountPerPage = 10;
        $expected->first            = 1;
        $expected->current          = 2;
        $expected->last             = 11;
        $expected->previous         = 1;
        $expected->next             = 3;
        $expected->pagesInRange     = array_combine(range(1, 10), range(1, 10));
        $expected->firstPageInRange = 1;
        $expected->lastPageInRange  = 10;
        $expected->currentItemCount = 10;
        $expected->totalItemCount   = 101;
        $expected->firstItemNumber  = 11;
        $expected->lastItemNumber   = 20;

        $this->paginator->setCurrentPageNumber(2);
        $actual = $this->paginator->getPages();

        $this->assertEquals($expected, $actual);
    }

    public function testRendersWithoutPartial(): void
    {
        $this->paginator->setView(new View\Renderer\PhpRenderer());
        $string = @$this->paginator->__toString();
        $this->assertEquals('', $string);
    }

    public function testRendersWithPartial(): void
    {
        $view = new View\Renderer\PhpRenderer();
        $view->resolver()->addPath(__DIR__ . '/_files/scripts');

        Helper\PaginationControl::setDefaultViewPartial('partial.phtml');

        $this->paginator->setView($view);

        $string = $this->paginator->__toString();
        $this->assertEquals('partial rendered successfully', $string);
    }

    public function testGetsPageCount(): void
    {
        $this->assertEquals(11, $this->paginator->count());
    }

    public function testGetsAndSetsItemCountPerPage(): void
    {
        Paginator\Paginator::setGlobalConfig(new Config\Config([]));
        $this->paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $this->assertEquals(10, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(15);
        $this->assertEquals(15, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    /**
     * @group Laminas-5376
     */
    public function testGetsAndSetsItemCounterPerPageOfNegativeOne(): void
    {
        Paginator\Paginator::setGlobalConfig(new Config\Config([]));
        $this->paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(-1);
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    /**
     * @group Laminas-5376
     */
    public function testGetsAndSetsItemCounterPerPageOfZero(): void
    {
        Paginator\Paginator::setGlobalConfig(new Config\Config([]));
        $this->paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    /**
     * @group Laminas-5376
     */
    public function testGetsAndSetsItemCounterPerPageOfNull(): void
    {
        Paginator\Paginator::setGlobalConfig(new Config\Config([]));
        $this->paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage();
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    public function testGetsCurrentItemCount(): void
    {
        $this->paginator->setItemCountPerPage(10);
        $this->paginator->setPageRange(10);

        $this->assertEquals(10, $this->paginator->getCurrentItemCount());

        $this->paginator->setCurrentPageNumber(11);

        $this->assertEquals(1, $this->paginator->getCurrentItemCount());

        $this->paginator->setCurrentPageNumber(1);
    }

    public function testGetsCurrentItems(): void
    {
        $items = $this->paginator->getCurrentItems();

        self::assertInstanceOf(ArrayIterator::class, $items);
        self::assertCount(10, $items);
        self::assertContainsOnly('integer', $items);
    }

    public function testGetsIterator(): void
    {
        $items = $this->paginator->getIterator();

        self::assertInstanceOf(ArrayIterator::class, $items);
        self::assertCount(10, $items);
        self::assertContainsOnly('integer', $items);
    }

    public function testGetsAndSetsCurrentPageNumber(): void
    {
        $this->assertEquals(1, $this->paginator->getCurrentPageNumber());
        $this->paginator->setCurrentPageNumber(-1);
        $this->assertEquals(1, $this->paginator->getCurrentPageNumber());
        $this->paginator->setCurrentPageNumber(11);
        $this->assertEquals(11, $this->paginator->getCurrentPageNumber());
        $this->paginator->setCurrentPageNumber(111);
        $this->assertEquals(11, $this->paginator->getCurrentPageNumber());
        $this->paginator->setCurrentPageNumber(1);
        $this->assertEquals(1, $this->paginator->getCurrentPageNumber());
    }

    public function testGetsAbsoluteItemNumber(): void
    {
        $this->assertEquals(1, $this->paginator->getAbsoluteItemNumber(1));
        $this->assertEquals(11, $this->paginator->getAbsoluteItemNumber(1, 2));
        $this->assertEquals(24, $this->paginator->getAbsoluteItemNumber(4, 3));
    }

    public function testGetsItem(): void
    {
        $this->assertEquals(1, $this->paginator->getItem(1));
        $this->assertEquals(11, $this->paginator->getItem(1, 2));
        $this->assertEquals(24, $this->paginator->getItem(4, 3));
    }

    public function testThrowsExceptionWhenCollectionIsEmpty(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page 1 does not exist');
        $paginator->getItem(1);
    }

    public function testThrowsExceptionWhenRetrievingNonexistentItemFromLastPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page 11 does not contain item number 10');
        $this->paginator->getItem(10, 11);
    }

    public function testNormalizesPageNumber(): void
    {
        $this->assertEquals(1, $this->paginator->normalizePageNumber(0));
        $this->assertEquals(1, $this->paginator->normalizePageNumber(1));
        $this->assertEquals(2, $this->paginator->normalizePageNumber(2));
        $this->assertEquals(5, $this->paginator->normalizePageNumber(5));
        $this->assertEquals(10, $this->paginator->normalizePageNumber(10));
        $this->assertEquals(11, $this->paginator->normalizePageNumber(11));
        $this->assertEquals(11, $this->paginator->normalizePageNumber(12));
    }

    public function testNormalizesItemNumber(): void
    {
        $this->assertEquals(1, $this->paginator->normalizeItemNumber(0));
        $this->assertEquals(1, $this->paginator->normalizeItemNumber(1));
        $this->assertEquals(2, $this->paginator->normalizeItemNumber(2));
        $this->assertEquals(5, $this->paginator->normalizeItemNumber(5));
        $this->assertEquals(9, $this->paginator->normalizeItemNumber(9));
        $this->assertEquals(10, $this->paginator->normalizeItemNumber(10));
        $this->assertEquals(10, $this->paginator->normalizeItemNumber(11));
    }

    /**
     * @group Laminas-8656
     */
    public function testNormalizesPageNumberWhenGivenAFloat(): void
    {
        $this->assertEquals(1, $this->paginator->normalizePageNumber(0.5));
        $this->assertEquals(1, $this->paginator->normalizePageNumber(1.99));
        $this->assertEquals(2, $this->paginator->normalizePageNumber(2.3));
        $this->assertEquals(5, $this->paginator->normalizePageNumber(5.1));
        $this->assertEquals(10, $this->paginator->normalizePageNumber(10.06));
        $this->assertEquals(11, $this->paginator->normalizePageNumber(11.5));
        $this->assertEquals(11, $this->paginator->normalizePageNumber(12.7889));
    }

    /**
     * @group Laminas-8656
     */
    public function testNormalizesItemNumberWhenGivenAFloat(): void
    {
        $this->assertEquals(1, $this->paginator->normalizeItemNumber(0.5));
        $this->assertEquals(1, $this->paginator->normalizeItemNumber(1.99));
        $this->assertEquals(2, $this->paginator->normalizeItemNumber(2.3));
        $this->assertEquals(5, $this->paginator->normalizeItemNumber(5.1));
        $this->assertEquals(9, $this->paginator->normalizeItemNumber(9.06));
        $this->assertEquals(10, $this->paginator->normalizeItemNumber(10.5));
        $this->assertEquals(10, $this->paginator->normalizeItemNumber(11.7889));
    }

    public function testGetsPagesInSubsetRange(): void
    {
        $actual = $this->paginator->getPagesInRange(3, 8);
        $this->assertEquals(array_combine(range(3, 8), range(3, 8)), $actual);
    }

    public function testGetsPagesInOutOfBoundsRange(): void
    {
        $actual = $this->paginator->getPagesInRange(-1, 12);
        $this->assertEquals(array_combine(range(1, 11), range(1, 11)), $actual);
    }

    public function testGetsItemsByPage(): void
    {
        $expected = new ArrayIterator(range(1, 10));

        $page1 = $this->paginator->getItemsByPage(1);

        $this->assertEquals($page1, $expected);
        $this->assertEquals($page1, $this->paginator->getItemsByPage(1));
    }

    public function testGetsItemsByPageHandle(): void
    {
        $iterator = new ArrayIterator([
            new ArrayObject(['foo' => 'bar']),
            new ArrayObject(['foo' => 'bar']),
            new ArrayObject(['foo' => 'bar']),
        ]);

        $paginator = new Paginator\Paginator(new Paginator\Adapter\Iterator($iterator));
        $items     = $paginator->getItemsByPage(1);

        $this->assertIsIterable($items);

        foreach ($items as $item) {
            $this->assertInstanceOf('ArrayObject', $item);
        }
    }

    public function testGetsItemCount(): void
    {
        $this->assertEquals(101, $this->paginator->getItemCount(range(1, 101)));

        $limitIterator = new LimitIterator(new ArrayIterator(range(1, 101)));
        $this->assertEquals(101, $this->paginator->getItemCount($limitIterator));
    }

    public function testGeneratesViewIfNonexistent(): void
    {
        $this->assertInstanceOf(RendererInterface::class, $this->paginator->getView());
    }

    public function testGetsAndSetsView(): void
    {
        $this->paginator->setView(new View\Renderer\PhpRenderer());
        $this->assertInstanceOf(RendererInterface::class, $this->paginator->getView());
    }

    public function testRenders(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage('view partial');
        $this->paginator->render(new View\Renderer\PhpRenderer());
    }

    public function testGetsAndSetsPageRange(): void
    {
        $this->assertEquals(10, $this->paginator->getPageRange());
        $this->paginator->setPageRange(15);
        $this->assertEquals(15, $this->paginator->getPageRange());
    }

    /**
     * @group Laminas-3720
     */
    public function testGivesCorrectItemCount(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $paginator->setCurrentPageNumber(5)
                  ->setItemCountPerPage(5);
        $expected = new ArrayIterator(range(21, 25));

        $this->assertEquals($expected, $paginator->getCurrentItems());
    }

    /**
     * @group Laminas-3737
     */
    public function testKeepsCurrentPageNumberAfterItemCountPerPageSet(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(['item1', 'item2']));
        $paginator->setCurrentPageNumber(2)
                  ->setItemCountPerPage(1);

        $items = $paginator->getCurrentItems();

        $this->assertEquals('item2', $items[0]);
    }

    /**
     * @group Laminas-4193
     */
    public function testCastsIntegerValuesToInteger(): void
    {
        // Current page number
        $this->paginator->setCurrentPageNumber(3.3);
        $this->assertEquals(3, $this->paginator->getCurrentPageNumber());

        // Item count per page
        $this->paginator->setItemCountPerPage(3.3);
        $this->assertEquals(3, $this->paginator->getItemCountPerPage());

        // Page range
        $this->paginator->setPageRange(3.3);
        $this->assertEquals(3, $this->paginator->getPageRange());
    }

    /**
     * @group Laminas-4207
     */
    public function testAcceptsTraversableInstanceFromAdapter(): void
    {
        $paginator = new Paginator\Paginator(new TestAsset\TestAdapter());
        $this->assertInstanceOf('ArrayObject', $paginator->getCurrentItems());
    }

    public function testCachedItem(): void
    {
        $this->paginator->setCurrentPageNumber(1)->getCurrentItems();
        $this->paginator->setCurrentPageNumber(2)->getCurrentItems();
        $this->paginator->setCurrentPageNumber(3)->getCurrentItems();

        // cache entry to check that paginator loads only own items
        $this->cache->addItem('not_paginator_item', 42);

        $pageItems = $this->paginator->getPageItemCache();
        $expected  = [
            1 => new ArrayIterator(range(1, 10)),
            2 => new ArrayIterator(range(11, 20)),
            3 => new ArrayIterator(range(21, 30)),
        ];
        $this->assertEquals($expected, $pageItems);
    }

    public function testClearPageItemCache(): void
    {
        $this->paginator->setCurrentPageNumber(1)->getCurrentItems();
        $this->paginator->setCurrentPageNumber(2)->getCurrentItems();
        $this->paginator->setCurrentPageNumber(3)->getCurrentItems();

        // cache entry to check that paginator deletes only own items
        $this->cache->addItem('not_paginator_item', 42);

        // clear only page 2 items
        $this->paginator->clearPageItemCache(2);
        $pageItems = $this->paginator->getPageItemCache();
        $expected  = [
            1 => new ArrayIterator(range(1, 10)),
            3 => new ArrayIterator(range(21, 30)),
        ];
        $this->assertEquals($expected, $pageItems);

        // clear all
        $this->paginator->clearPageItemCache();
        $pageItems = $this->paginator->getPageItemCache();
        $this->assertEquals([], $pageItems);

        // assert that cache items not from paginator are not cleared
        $this->assertEquals(42, $this->cache->getItem('not_paginator_item'));
    }

    public function testWithCacheDisabled(): void
    {
        $this->paginator->setCacheEnabled(false);
        $this->paginator->setCurrentPageNumber(1)->getCurrentItems();

        $cachedPageItems = $this->paginator->getPageItemCache();
        $expected        = new ArrayIterator(range(1, 10));

        $this->assertEquals([], $cachedPageItems);

        $pageItems = $this->paginator->getCurrentItems();

        $this->assertEquals($expected, $pageItems);
    }

    public function testCacheDoesNotDisturbResultsWhenChangingParam(): void
    {
        $this->paginator->setCurrentPageNumber(1)->getCurrentItems();
        $pageItems = $this->paginator->setItemCountPerPage(5)->getCurrentItems();

        $expected = new ArrayIterator(range(1, 5));
        $this->assertEquals($expected, $pageItems);

        $pageItems = $this->paginator->getItemsByPage(2);
        $expected  = new ArrayIterator(range(6, 10));
        $this->assertEquals($expected, $pageItems);

        // change the inside Paginator scale
        $this->paginator->setItemCountPerPage(8)->setCurrentPageNumber(3)->getCurrentItems();

        $pageItems = $this->paginator->getPageItemCache();
        $expected  = new ArrayIterator(range(17, 24)); /*array(3 => */ /*) */
        $this->assertEquals($expected, $pageItems[3]);

        // get back to already cached data
        $this->paginator->setItemCountPerPage(5);
        $pageItems = $this->paginator->getPageItemCache();
        $expected  = [
            1 => new ArrayIterator(range(1, 5)),
            2 => new ArrayIterator(range(6, 10)),
        ];
        $this->assertEquals($expected[1], $pageItems[1]);
        $this->assertEquals($expected[2], $pageItems[2]);
    }

    public function testToJson(): void
    {
        $this->paginator->setCurrentPageNumber(1);

        $json = $this->paginator->toJson();

        $expected = '"0":1,"1":2,"2":3,"3":4,"4":5,"5":6,"6":7,"7":8,"8":9,"9":10';

        $this->assertStringContainsString($expected, $json);
    }

    public function testFilter(): void
    {
        $filter    = new Filter\Callback([$this, 'filterCallback']);
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $paginator->setFilter($filter);

        $page = $paginator->getCurrentItems();

        $this->assertEquals(new ArrayIterator(range(10, 100, 10)), $page);
    }

    /**
     * @param int[] $value
     */
    public function filterCallback($value): array
    {
        $data = [];

        foreach ($value as $number) {
            $data[] = $number * 10;
        }

        return $data;
    }

    /**
     * @group Laminas-5785
     */
    public function testGetSetDefaultItemCountPerPage(): void
    {
        Paginator\Paginator::setGlobalConfig(new Config\Config([]));

        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 10)));
        $this->assertEquals(10, $paginator->getItemCountPerPage());

        Paginator\Paginator::setDefaultItemCountPerPage(20);
        $this->assertEquals(20, Paginator\Paginator::getDefaultItemCountPerPage());

        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 10)));
        $this->assertEquals(20, $paginator->getItemCountPerPage());

        $this->_restorePaginatorDefaults();
    }

    /**
     * @group Laminas-7207
     */
    public function testItemCountPerPageByDefault(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 20)));
        $this->assertEquals(2, $paginator->count());
    }

    /**
     * @group Laminas-5427
     */
    public function testNegativeItemNumbers(): void
    {
        $this->assertEquals(10, $this->paginator->getItem(-1, 1));
        $this->assertEquals(9, $this->paginator->getItem(-2, 1));
        $this->assertEquals(101, $this->paginator->getItem(-1, -1));
    }

    /**
     * @group Laminas-7602
     */
    public function testAcceptAndHandlePaginatorAdapterAggregateDataInFactory(): void
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertEquals(1, count($p));
        $this->assertInstanceOf(ArrayAdapter::class, $p->getAdapter());
        $this->assertEquals(4, count($p->getAdapter()));
    }

    /**
     * @group Laminas-7602
     */
    public function testAcceptAndHandlePaginatorAdapterAggregateInConstructor(): void
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertEquals(1, count($p));
        $this->assertInstanceOf(ArrayAdapter::class, $p->getAdapter());
        $this->assertEquals(4, count($p->getAdapter()));
    }

    /**
     * @group Laminas-7602
     */
    public function testInvalidDataInConstructorThrowsException(): void
    {
        // @codingStandardsIgnoreEnd
        $this->expectException(\Laminas\Paginator\Exception\ExceptionInterface::class);

        new Paginator\Paginator([]);
    }

    /**
     * @group Laminas-9396
     */
    public function testArrayAccessInClassSerializableLimitIterator(): void
    {
        $iterator  = new ArrayIterator(['laminas9396', 'foo', null]);
        $paginator = new Paginator\Paginator(new Adapter\Iterator($iterator));

        $this->assertEquals('laminas9396', $paginator->getItem(1));

        /** @psalm-var SerializableLimitIterator $items */
        $items = $paginator->getAdapter()
                           ->getItems(0, 10);

        $this->assertEquals('foo', $items[1]);
        $this->assertEquals(0, $items->key());
        $this->assertFalse(isset($items[2]));
        $this->assertTrue(isset($items[1]));
        $this->assertFalse(isset($items[3]));
    }

    public function testSetGlobalConfigThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('setGlobalConfig expects an array or Traversable');

        $this->paginator->setGlobalConfig('not array');
    }

    public function testSetScrollingStylePluginManagerWithStringThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to locate scrolling style plugin manager with class "invalid adapter"; class not found'
        );

        $this->paginator->setScrollingStylePluginManager('invalid adapter');
    }

    public function testSetScrollingStylePluginManagerWithAdapterThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Pagination scrolling-style manager must extend ScrollingStylePluginManager; received "stdClass"'
        );

        $this->paginator->setScrollingStylePluginManager(
            new stdClass()
        );
    }

    public function testLoadScrollingStyleWithDigitThrowsInvalidArgumentException(): void
    {
        $adapter    = new TestAsset\TestAdapter();
        $paginator  = new Paginator\Paginator($adapter);
        $reflection = new ReflectionMethod($paginator, '_loadScrollingStyle');
        $reflection->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Scrolling style must be a class '
                . 'name or object implementing Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface'
        );

        $reflection->invoke($paginator, 12345);
    }

    public function testLoadScrollingStyleWithObjectThrowsInvalidArgumentException(): void
    {
        $adapter    = new TestAsset\TestAdapter();
        $paginator  = new Paginator\Paginator($adapter);
        $reflection = new ReflectionMethod($paginator, '_loadScrollingStyle');
        $reflection->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Scrolling style must implement Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface'
        );

        $reflection->invoke($paginator, new stdClass());
    }

    public function testGetCacheId(): void
    {
        $adapter              = new TestAsset\TestAdapter();
        $paginator            = new Paginator\Paginator($adapter);
        $reflectionGetCacheId = new ReflectionMethod($paginator, '_getCacheId');
        $reflectionGetCacheId->setAccessible(true);
        $outputGetCacheId = $reflectionGetCacheId->invoke($paginator, null);

        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        $outputGetCacheInternalId = $reflectionGetCacheInternalId->invoke($paginator);

        $this->assertEquals($outputGetCacheId, 'Laminas_Paginator_1_' . $outputGetCacheInternalId);

        // After a re-creation of the same object, cacheId should remains the same
        $adapter                      = new TestAsset\TestAdapter();
        $paginator                    = new Paginator\Paginator($adapter);
        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        $outputGetCacheInternalId = $reflectionGetCacheInternalId->invoke($paginator);
        $this->assertEquals($outputGetCacheId, 'Laminas_Paginator_1_' . $outputGetCacheInternalId);
    }

    public function testGetCacheIdWithSameAdapterAndDifferentAttributes(): void
    {
        $adapter   = new TestAsset\TestAdapter([1, 2, 3, 4]);
        $paginator = new Paginator\Paginator($adapter);

        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        $firstOutputGetCacheInternalId = $reflectionGetCacheInternalId->invoke($paginator);

        $adapter                      = new TestAsset\TestAdapter([1, 2, 3, 4, 5, 6]);
        $paginator                    = new Paginator\Paginator($adapter);
        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        $secondOutputGetCacheInternalId = $reflectionGetCacheInternalId->invoke($paginator);
        $this->assertNotEquals($firstOutputGetCacheInternalId, $secondOutputGetCacheInternalId);
    }

    public function testGetCacheIdWithInheritedClass(): void
    {
        $adapter   = new TestAsset\TestAdapter([1, 2, 3, 4]);
        $paginator = new Paginator\Paginator($adapter);

        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        $firstOutputGetCacheInternalId = $reflectionGetCacheInternalId->invoke($paginator);

        $adapter                      = new TestAsset\TestSimilarAdapter([1, 2, 3, 4]);
        $paginator                    = new Paginator\Paginator($adapter);
        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        $secondOutputGetCacheInternalId = $reflectionGetCacheInternalId->invoke($paginator);
        $this->assertNotEquals($firstOutputGetCacheInternalId, $secondOutputGetCacheInternalId);
    }

    public function testPaginatorShouldProduceDifferentCacheIdsWithDifferentAdapterInstances(): void
    {
        // Create first interal cache ID
        $paginator                    = new Paginator\Paginator(new TestAsset\TestAdapter('foo'));
        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        /** @var string $firstCacheId */
        $firstCacheId = $reflectionGetCacheInternalId->invoke($paginator);

        // Create second internal cache ID
        $paginator                    = new Paginator\Paginator(new TestAsset\TestAdapter('bar'));
        $reflectionGetCacheInternalId = new ReflectionMethod($paginator, '_getCacheInternalId');
        $reflectionGetCacheInternalId->setAccessible(true);
        /** @var string $secondCacheId */
        $secondCacheId = $reflectionGetCacheInternalId->invoke($paginator);

        // Test
        $this->assertNotEquals($firstCacheId, $secondCacheId);
    }

    public function testAcceptsComplexAdapters(): void
    {
        $paginator = new Paginator\Paginator(
            new TestAsset\TestAdapter(function () {
                return 'test';
            })
        );
        $this->assertInstanceOf('ArrayObject', $paginator->getCurrentItems());
    }

    /**
     * @group 6808
     * @group 6809
     */
    public function testItemCountsForEmptyItemSet(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter([]));
        $paginator->setCurrentPageNumber(1);

        $expected                   = new stdClass();
        $expected->pageCount        = 0;
        $expected->itemCountPerPage = 10;
        $expected->first            = 1;
        $expected->current          = 1;
        $expected->last             = 0;
        $expected->pagesInRange     = [1 => 1];
        $expected->firstPageInRange = 1;
        $expected->lastPageInRange  = 1;
        $expected->currentItemCount = 0;
        $expected->totalItemCount   = 0;
        $expected->firstItemNumber  = 0;
        $expected->lastItemNumber   = 0;

        $actual = $paginator->getPages();

        $this->assertEquals($expected, $actual);
    }
}
