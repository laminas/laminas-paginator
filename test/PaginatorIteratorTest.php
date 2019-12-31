<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\PaginatorIterator;

class PaginatorIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorFlattensPaginator()
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

    public function testIteratorReturnsInvalidOnEmptyIterator()
    {
        $paginator = new Paginator(
            new ArrayAdapter([])
        );

        $iterator = new PaginatorIterator($paginator);

        $iterator->rewind();
        $this->assertFalse($iterator->valid());
    }
}
