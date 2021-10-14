<?php

namespace LaminasTest\Paginator;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\PaginatorIterator;
use PHPUnit\Framework\TestCase;

class PaginatorIteratorTest extends TestCase
{
    public function testIteratorFlattensPaginator(): void
    {
        $paginator = new Paginator(
            new ArrayAdapter(['foo', 'bar', 'fiz'])
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
            new ArrayAdapter([])
        );

        $iterator = new PaginatorIterator($paginator);

        $iterator->rewind();
        $this->assertFalse($iterator->valid());
    }
}
