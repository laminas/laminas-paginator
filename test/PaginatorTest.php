<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Iterator;
use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\Paginator\ScrollingStyle\Jumping;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStyle\Sliding;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;
use LimitIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Traversable;

use function array_combine;
use function array_map;
use function assert;
use function chr;
use function is_array;
use function iterator_to_array;
use function range;

final class PaginatorTest extends TestCase
{
    public function testHasCorrectCountAfterInit(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(11, $paginator->count());
    }

    public function testHasCorrectCountOfAllItemsAfterInit(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(101, $paginator->getTotalItemCount());
    }

    public function testRepetitiveCallOfCountResultsOfZero(): void
    {
        $count = 0;

        $paginator = new Paginator\Paginator(new ArrayAdapter([]));
        $this->assertEquals($count, $paginator->count());
        $this->assertEquals($count, $paginator->count());
    }

    public function testPageSizeAndRangeCanBeSetViaConstructor(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)), 3, 7);
        $this->assertEquals(3, $paginator->getItemCountPerPage());
        $this->assertEquals(7, $paginator->getPageRange());
    }

    public function testGetsPagesForPageOne(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));

        $expected = new Paginator\Pages(
            11,
            10,
            1,
            1,
            11,
            null,
            2,
            array_combine(range(1, 10), range(1, 10)),
            1,
            10,
            10,
            101,
            1,
            10,
        );

        $this->assertEquals($expected, $paginator->getPages());
    }

    public function testGetsPagesForPageTwo(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $expected  = new Paginator\Pages(
            11,
            10,
            1,
            2,
            11,
            1,
            3,
            array_combine(range(1, 10), range(1, 10)),
            1,
            10,
            10,
            101,
            11,
            20,
        );

        $paginator->setCurrentPageNumber(2);
        $this->assertEquals($expected, $paginator->getPages());
    }

    public function testGetsPageCount(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(11, $paginator->count());
    }

    public function testGetsAndSetsItemCountPerPage(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(10, $paginator->getItemCountPerPage());
        $paginator->setItemCountPerPage(15);
        $this->assertEquals(15, $paginator->getItemCountPerPage());
        $paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $paginator->getItemCountPerPage());
    }

    #[Group('Laminas-5376')]
    public function testGetsAndSetsItemCounterPerPageOfNegativeOne(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $paginator->setItemCountPerPage(-1);
        $this->assertEquals(101, $paginator->getItemCountPerPage());
    }

    #[Group('Laminas-5376')]
    public function testGetsAndSetsItemCounterPerPageOfZero(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $paginator->getItemCountPerPage());
    }

    #[Group('Laminas-5376')]
    public function testGetsAndSetsItemCounterPerPageOfNull(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $paginator->setItemCountPerPage();
        $this->assertEquals(101, $paginator->getItemCountPerPage());
    }

    public function testGetsCurrentItemCount(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));

        $this->assertEquals(10, $paginator->getCurrentItemCount());

        $paginator->setCurrentPageNumber(11);

        $this->assertEquals(1, $paginator->getCurrentItemCount());
    }

    public function testGetsCurrentItems(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $items     = $paginator->getCurrentItems();

        self::assertCount(10, $items);
        self::assertContainsOnlyInt($items);
    }

    public function testGetsIterator(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $items     = $paginator->getIterator();

        self::assertInstanceOf(ArrayIterator::class, $items);
        self::assertCount(10, $items);
        self::assertContainsOnlyInt($items);
    }

    public function testGetsAndSetsCurrentPageNumber(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(1, $paginator->getCurrentPageNumber());
        /** @psalm-suppress InvalidArgument */
        $paginator->setCurrentPageNumber(-1);
        $this->assertEquals(1, $paginator->getCurrentPageNumber());
        $paginator->setCurrentPageNumber(11);
        $this->assertEquals(11, $paginator->getCurrentPageNumber());
        $paginator->setCurrentPageNumber(111);
        $this->assertEquals(11, $paginator->getCurrentPageNumber());
        $paginator->setCurrentPageNumber(1);
        $this->assertEquals(1, $paginator->getCurrentPageNumber());
    }

    public function testGetsAbsoluteItemNumber(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(1, $paginator->getAbsoluteItemNumber(1));
        $this->assertEquals(11, $paginator->getAbsoluteItemNumber(1, 2));
        $this->assertEquals(24, $paginator->getAbsoluteItemNumber(4, 3));
    }

    public function testGetsItem(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(1, $paginator->getItem(1));
        $this->assertEquals(11, $paginator->getItem(1, 2));
        $this->assertEquals(24, $paginator->getItem(4, 3));
    }

    public function testThrowsExceptionWhenCollectionIsEmpty(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page 1 does not exist');
        $paginator->getItem(1);
    }

    public function testThrowsExceptionWhenRetrievingNonexistentItemFromLastPage(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page 11 does not contain item number 10');
        $paginator->getItem(10, 11);
    }

    public function testNormalizesPageNumber(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(1, $paginator->normalizePageNumber(0));
        $this->assertEquals(1, $paginator->normalizePageNumber(1));
        $this->assertEquals(2, $paginator->normalizePageNumber(2));
        $this->assertEquals(5, $paginator->normalizePageNumber(5));
        $this->assertEquals(10, $paginator->normalizePageNumber(10));
        $this->assertEquals(11, $paginator->normalizePageNumber(11));
        $this->assertEquals(11, $paginator->normalizePageNumber(12));
    }

    public function testNormalizesItemNumber(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(1, $paginator->normalizeItemNumber(0));
        $this->assertEquals(1, $paginator->normalizeItemNumber(1));
        $this->assertEquals(2, $paginator->normalizeItemNumber(2));
        $this->assertEquals(5, $paginator->normalizeItemNumber(5));
        $this->assertEquals(9, $paginator->normalizeItemNumber(9));
        $this->assertEquals(10, $paginator->normalizeItemNumber(10));
        $this->assertEquals(10, $paginator->normalizeItemNumber(11));
    }

    public function testGetsPagesInSubsetRange(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $actual    = $paginator->getPagesInRange(3, 8);
        $this->assertEquals(array_combine(range(3, 8), range(3, 8)), $actual);
    }

    public function testGetsPagesInOutOfBoundsRange(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $actual    = $paginator->getPagesInRange(-1, 12);
        $this->assertEquals(array_combine(range(1, 11), range(1, 11)), $actual);
    }

    public function testGetsItemsByPage(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $expected  = new ArrayIterator(range(1, 10));

        $page1 = $paginator->getItemsByPage(1);

        $this->assertEquals($page1, $expected);
        $this->assertEquals($page1, $paginator->getItemsByPage(1));
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
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(101, $paginator->getItemCount(range(1, 101)));

        $limitIterator = new LimitIterator(new ArrayIterator(range(1, 101)));
        $this->assertEquals(101, $paginator->getItemCount($limitIterator));
    }

    public function testGetsAndSetsPageRange(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(10, $paginator->getPageRange());
        $paginator->setPageRange(15);
        $this->assertEquals(15, $paginator->getPageRange());
    }

    #[Group('Laminas-3720')]
    public function testGivesCorrectItemCount(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $paginator->setCurrentPageNumber(5)
                  ->setItemCountPerPage(5);
        $expected = new ArrayIterator(range(21, 25));

        $this->assertEquals($expected, $paginator->getCurrentItems());
    }

    #[Group('Laminas-3737')]
    public function testKeepsCurrentPageNumberAfterItemCountPerPageSet(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(['item1', 'item2']));
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

    #[Group('Laminas-7207')]
    public function testItemCountPerPageByDefault(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 20)));
        $this->assertCount(2, $paginator);
    }

    #[Group('Laminas-5427')]
    public function testNegativeItemNumbers(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter(range(1, 101)));
        $this->assertEquals(10, $paginator->getItem(-1, 1));
        $this->assertEquals(9, $paginator->getItem(-2, 1));
        $this->assertEquals(101, $paginator->getItem(-1, -1));
    }

    #[Group('Laminas-7602')]
    public function testAcceptAndHandlePaginatorAdapterAggregateDataInFactory(): void
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertCount(1, $p);
        $this->assertInstanceOf(ArrayAdapter::class, $p->getAdapter());
        $this->assertCount(4, $p->getAdapter());
    }

    #[Group('Laminas-7602')]
    public function testAcceptAndHandlePaginatorAdapterAggregateInConstructor(): void
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertCount(1, $p);
        $this->assertInstanceOf(ArrayAdapter::class, $p->getAdapter());
        $this->assertCount(4, $p->getAdapter());
    }

    #[Group('Laminas-9396')]
    public function testArrayAccessInClassSerializableLimitIterator(): void
    {
        $iterator  = new ArrayIterator(['laminas9396', 'foo', null]);
        $paginator = new Paginator\Paginator(new Adapter\Iterator($iterator));

        $this->assertEquals('laminas9396', $paginator->getItem(1));

        $items = $paginator->getAdapter()
                           ->getItems(0, 10);

        assert($items instanceof Iterator);
        assert($items instanceof ArrayAccess);

        $this->assertEquals(0, $items->key());
        $this->assertTrue(isset($items[1]));
        $this->assertFalse(isset($items[2]));
        $this->assertFalse(isset($items[3]));
        $this->assertEquals('foo', $items[1]);
    }

    /** @return list<array{0: string|null}> */
    public static function nonObjectScrollingStyleValues(): array
    {
        return [
            [null],
            ['foo'],
            [''],
            ['1'],
        ];
    }

    #[DataProvider('nonObjectScrollingStyleValues')]
    public function testTheDefaultScrollingStyleWillBeUsedForVariousArgumentsInConstructor(string|null $style): void
    {
        $instance = new Paginator\Paginator(new ArrayAdapter(range(0, 199)), 10, 5, $style);
        $instance->setCurrentPageNumber(5);
        $pages = $instance->getPages();

        self::assertCount(5, $pages->pagesInRange);
        self::assertSame([
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
        ], $pages->pagesInRange);
    }

    #[DataProvider('nonObjectScrollingStyleValues')]
    public function testTheDefaultScrollingStyleWillBeUsedForVariousArgumentsInGetPages(string|null $style): void
    {
        $instance = new Paginator\Paginator(new ArrayAdapter(range(0, 199)), 10, 5);
        $instance->setCurrentPageNumber(5);
        $pages = $instance->getPages($style);

        self::assertCount(5, $pages->pagesInRange);
        self::assertSame([
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
        ], $pages->pagesInRange);
    }

    /** @return list<array{0: string|ScrollingStyleInterface}> */
    public static function jumpingScrollingStyleValues(): array
    {
        return [
            ['jumping'],
            ['Jumping'],
            ['JUmPing'],
            [new Jumping()],
        ];
    }

    #[DataProvider('jumpingScrollingStyleValues')]
    public function testTheGivenScrollingStyleWillBeUsedWithConstructor(string|ScrollingStyleInterface $style): void
    {
        $instance = new Paginator\Paginator(new ArrayAdapter(range(0, 199)), 10, 5, $style);
        $instance->setCurrentPageNumber(6);
        $pages = $instance->getPages();

        self::assertCount(5, $pages->pagesInRange);
        self::assertSame([
            6  => 6,
            7  => 7,
            8  => 8,
            9  => 9,
            10 => 10,
        ], $pages->pagesInRange);
    }

    #[DataProvider('jumpingScrollingStyleValues')]
    public function testTheGivenScrollingStyleWillBeUsedWithGetPages(string|ScrollingStyleInterface $style): void
    {
        $instance = new Paginator\Paginator(new ArrayAdapter(range(0, 199)), 10, 5);
        $instance->setCurrentPageNumber(6);
        $pages = $instance->getPages($style);

        self::assertCount(5, $pages->pagesInRange);
        self::assertSame([
            6  => 6,
            7  => 7,
            8  => 8,
            9  => 9,
            10 => 10,
        ], $pages->pagesInRange);
    }

    public function testTheScrollingStyleGivenToGetPagesOverridesTheConstructorDefault(): void
    {
        $instance = new Paginator\Paginator(new ArrayAdapter(range(0, 199)), 10, 5, new Jumping());
        $instance->setCurrentPageNumber(6);
        $pages = $instance->getPages(new Sliding());

        self::assertCount(5, $pages->pagesInRange);
        self::assertSame([
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
        ], $pages->pagesInRange);
    }

    public function testAcceptsComplexAdapters(): void
    {
        $paginator = new Paginator\Paginator(
            new TestAsset\TestAdapter(static fn(): string => 'test'),
        );
        $this->assertInstanceOf(ArrayObject::class, $paginator->getCurrentItems());
    }

    #[Group('6808')]
    #[Group('6809')]
    public function testItemCountsForEmptyItemSet(): void
    {
        $paginator = new Paginator\Paginator(new ArrayAdapter([]));
        $paginator->setCurrentPageNumber(1);

        $expected = new Paginator\Pages(
            0,
            10,
            1,
            1,
            1,
            null,
            null,
            [1 => 1],
            1,
            1,
            0,
            0,
            0,
            0,
        );

        $this->assertEquals($expected, $paginator->getPages());
    }

    public function testItemRetrievalWithStringKeys(): void
    {
        $keys = array_map(
            fn (int $i): string => chr($i),
            range(65, 90),
        );
        $data = array_combine(
            $keys,
            $keys,
        );

        $paginator = new Paginator\Paginator(new ArrayAdapter($data));
        $paginator->setItemCountPerPage(5);

        $expect = [
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
        ];

        $items = $paginator->getCurrentItems();
        self::assertInstanceOf(Traversable::class, $items);
        self::assertCount(5, $items);
        $items = iterator_to_array($items);
        self::assertEquals($expect, $items);

        $item = $paginator->getItem(1, 5);
        self::assertSame('U', $item);
        self::assertSame(21, $paginator->getAbsoluteItemNumber(1, 5));
        self::assertSame('Z', $paginator->getAbsoluteItemNumber('Z', 6));
    }
}
