<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\PaginatorIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function array_fill;
use function array_map;
use function chr;
use function count;
use function iterator_to_array;
use function range;

final class PaginatorIteratorTest extends TestCase
{
    /** @return list<array{0: positive-int}> */
    public static function perPageItemCount(): array
    {
        return [
            [10],
            [20],
            [5],
            [100],
        ];
    }

    /** @param positive-int $itemCountPerPage */
    #[DataProvider('perPageItemCount')]
    public function testPrimaryUseCase(int $itemCountPerPage): void
    {
        $range     = range(0, 999);
        $paginator = new Paginator(new ArrayAdapter($range), $itemCountPerPage);
        $iterator  = new PaginatorIterator($paginator);

        self::assertSame($range, iterator_to_array($iterator));
    }

    /** @param positive-int $itemCountPerPage */
    #[DataProvider('perPageItemCount')]
    public function testIteratorWithStringKeys(int $itemCountPerPage): void
    {
        $keys = array_map(
            chr(...),
            range(65, 122),
        );
        $data = array_combine(
            $keys,
            array_fill(0, count($keys), 'Muppet'),
        );

        $paginator = new Paginator(new ArrayAdapter($data), $itemCountPerPage);
        $iterator  = new PaginatorIterator($paginator);

        self::assertSame($data, iterator_to_array($iterator));
    }

    public function testIteratorFlattensPaginator(): void
    {
        $paginator = new Paginator(
            new ArrayAdapter(['foo', 'bar', 'fiz']),
        );

        $paginator->setItemCountPerPage(2);

        $iterator = new PaginatorIterator($paginator);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertEquals('foo', $iterator->current());
        $this->assertEquals(0, $iterator->key());
        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertEquals('bar', $iterator->current());
        $this->assertEquals(1, $iterator->key());
        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertEquals('fiz', $iterator->current());
        $this->assertEquals(2, $iterator->key());
        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function testIteratorReturnsInvalidOnEmptyIterator(): void
    {
        $paginator = new Paginator(
            new ArrayAdapter([]),
        );

        $iterator = new PaginatorIterator($paginator);

        $iterator->rewind();
        $this->assertFalse($iterator->valid());
    }
}
