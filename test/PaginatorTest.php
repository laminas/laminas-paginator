<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\Paginator\SerializableLimitIterator;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;
use LimitIterator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use stdClass;
use Traversable;

use function array_combine;
use function assert;
use function count;
use function is_array;
use function range;

final class PaginatorTest extends TestCase
{
    private Paginator\Paginator $paginator;

    protected function setUp(): void
    {
        $testCollection  = range(1, 101);
        $this->paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter($testCollection));

        $this->restorePaginatorDefaults();
    }

    private function restorePaginatorDefaults(): void
    {
        $this->paginator->setItemCountPerPage(10);
        $this->paginator->setCurrentPageNumber(1);
        $this->paginator->setPageRange(10);

        Paginator\Paginator::setDefaultScrollingStyle();

        Paginator\Paginator::setGlobalConfig([
            'itemcountperpage' => 10,
            'pagerange'        => 10,
        ]);

        Paginator\Paginator::setScrollingStylePluginManager(new Paginator\ScrollingStylePluginManager(
            $this->createMock(ContainerInterface::class)
        ));
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

        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter([]));
        $this->assertEquals($count, $paginator->count());
        $this->assertEquals($count, $paginator->count());
    }

    public function testLoadsFromConfig(): void
    {
        Paginator\Paginator::setGlobalConfig([
            'itemcountperpage' => 3,
            'pagerange'        => 7,
        ]);
        $this->assertEquals('Sliding', Paginator\Paginator::getDefaultScrollingStyle());

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

    public function testGetsPageCount(): void
    {
        $this->assertEquals(11, $this->paginator->count());
    }

    public function testGetsAndSetsItemCountPerPage(): void
    {
        Paginator\Paginator::setGlobalConfig([]);
        $this->paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $this->assertEquals(10, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(15);
        $this->assertEquals(15, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    #[Group('Laminas-5376')]
    public function testGetsAndSetsItemCounterPerPageOfNegativeOne(): void
    {
        Paginator\Paginator::setGlobalConfig([]);
        $this->paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(-1);
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    #[Group('Laminas-5376')]
    public function testGetsAndSetsItemCounterPerPageOfZero(): void
    {
        Paginator\Paginator::setGlobalConfig([]);
        $this->paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $this->paginator->getItemCountPerPage());
        $this->paginator->setItemCountPerPage(10);
    }

    #[Group('Laminas-5376')]
    public function testGetsAndSetsItemCounterPerPageOfNull(): void
    {
        Paginator\Paginator::setGlobalConfig([]);
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

        self::assertCount(10, $items);
        self::assertContainsOnlyInt($items);
    }

    public function testGetsIterator(): void
    {
        $items = $this->paginator->getIterator();

        self::assertInstanceOf(ArrayIterator::class, $items);
        self::assertCount(10, $items);
        self::assertContainsOnlyInt($items);
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

        self::assertInstanceOf(Traversable::class, $items);

        foreach ($items as $item) {
            $this->assertInstanceOf(ArrayObject::class, $item);
        }
    }

    public function testGetsItemCount(): void
    {
        $this->assertEquals(101, $this->paginator->getItemCount(range(1, 101)));

        $limitIterator = new LimitIterator(new ArrayIterator(range(1, 101)));
        $this->assertEquals(101, $this->paginator->getItemCount($limitIterator));
    }

    public function testGetsAndSetsPageRange(): void
    {
        $this->assertEquals(10, $this->paginator->getPageRange());
        $this->paginator->setPageRange(15);
        $this->assertEquals(15, $this->paginator->getPageRange());
    }

    #[Group('Laminas-3720')]
    public function testGivesCorrectItemCount(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $paginator->setCurrentPageNumber(5)
                  ->setItemCountPerPage(5);
        $expected = new ArrayIterator(range(21, 25));

        $this->assertEquals($expected, $paginator->getCurrentItems());
    }

    #[Group('Laminas-3737')]
    public function testKeepsCurrentPageNumberAfterItemCountPerPageSet(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(['item1', 'item2']));
        $paginator->setCurrentPageNumber(2)
                  ->setItemCountPerPage(1);

        $items = $paginator->getCurrentItems();
        assert(is_array($items) || $items instanceof ArrayAccess);

        $this->assertEquals('item2', $items[0]);
    }

    #[Group('Laminas-4207')]
    public function testAcceptsTraversableInstanceFromAdapter(): void
    {
        $paginator = new Paginator\Paginator(new TestAsset\TestAdapter());
        $this->assertInstanceOf(ArrayObject::class, $paginator->getCurrentItems());
    }

    public function testToJson(): void
    {
        $this->paginator->setCurrentPageNumber(1);

        $json = $this->paginator->toJson();

        $expected = '"0":1,"1":2,"2":3,"3":4,"4":5,"5":6,"6":7,"7":8,"8":9,"9":10';

        $this->assertStringContainsString($expected, $json);
    }

    #[Group('Laminas-5785')]
    public function testGetSetDefaultItemCountPerPage(): void
    {
        Paginator\Paginator::setGlobalConfig([]);

        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 10)));
        $this->assertEquals(10, $paginator->getItemCountPerPage());

        Paginator\Paginator::setDefaultItemCountPerPage(20);
        $this->assertEquals(20, Paginator\Paginator::getDefaultItemCountPerPage());

        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 10)));
        $this->assertEquals(20, $paginator->getItemCountPerPage());

        $this->restorePaginatorDefaults();
    }

    #[Group('Laminas-7207')]
    public function testItemCountPerPageByDefault(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 20)));
        $this->assertEquals(2, $paginator->count());
    }

    #[Group('Laminas-5427')]
    public function testNegativeItemNumbers(): void
    {
        $this->assertEquals(10, $this->paginator->getItem(-1, 1));
        $this->assertEquals(9, $this->paginator->getItem(-2, 1));
        $this->assertEquals(101, $this->paginator->getItem(-1, -1));
    }

    #[Group('Laminas-7602')]
    public function testAcceptAndHandlePaginatorAdapterAggregateDataInFactory(): void
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertEquals(1, count($p));
        $this->assertInstanceOf(ArrayAdapter::class, $p->getAdapter());
        $this->assertEquals(4, count($p->getAdapter()));
    }

    #[Group('Laminas-7602')]
    public function testAcceptAndHandlePaginatorAdapterAggregateInConstructor(): void
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertEquals(1, count($p));
        $this->assertInstanceOf(ArrayAdapter::class, $p->getAdapter());
        $this->assertEquals(4, count($p->getAdapter()));
    }

    #[Group('Laminas-9396')]
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

        /** @psalm-suppress InvalidArgument */
        $this->paginator->setScrollingStylePluginManager(
            new stdClass()
        );
    }

    public function testLoadScrollingStyleWithDigitThrowsInvalidArgumentException(): void
    {
        $adapter    = new TestAsset\TestAdapter();
        $paginator  = new Paginator\Paginator($adapter);
        $reflection = new ReflectionMethod($paginator, '_loadScrollingStyle');

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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Scrolling style must implement Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface'
        );

        $reflection->invoke($paginator, new stdClass());
    }

    public function testAcceptsComplexAdapters(): void
    {
        $paginator = new Paginator\Paginator(
            new TestAsset\TestAdapter(static fn(): string => 'test')
        );
        $this->assertInstanceOf(ArrayObject::class, $paginator->getCurrentItems());
    }

    #[Group('6808')]
    #[Group('6809')]
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
