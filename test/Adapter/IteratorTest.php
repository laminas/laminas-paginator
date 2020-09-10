<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter;
use Laminas\Paginator\Paginator;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Adapter\Iterator<extended>
 */
class IteratorTest extends TestCase
{
    /**
     * @var \Laminas\Paginator\Adapter\Iterator
     */
    private $adapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $iterator = new \ArrayIterator(range(1, 101));
        $this->adapter = new Adapter\Iterator($iterator);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testGetsItemsAtOffsetZero()
    {
        $actual = $this->adapter->getItems(0, 10);
        $this->assertInstanceOf('LimitIterator', $actual);

        $i = 1;
        foreach ($actual as $item) {
            $this->assertEquals($i, $item);
            $i++;
        }
    }

    public function testGetsItemsAtOffsetTen()
    {
        $actual = $this->adapter->getItems(10, 10);
        $this->assertInstanceOf('LimitIterator', $actual);

        $i = 11;
        foreach ($actual as $item) {
            $this->assertEquals($i, $item);
            $i++;
        }
    }

    public function testReturnsCorrectCount()
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    public function testThrowsExceptionIfNotCountable()
    {
        $iterator = new \LimitIterator(new \ArrayIterator(range(1, 101)));

        $this->expectException('Laminas\Paginator\Adapter\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Iterator must implement Countable');
        new Adapter\Iterator($iterator);
    }

    /**
     * @group Laminas-4151
     */
    public function testDoesNotThrowOutOfBoundsExceptionIfIteratorIsEmpty()
    {
        $this->paginator = new Paginator(new Adapter\Iterator(new \ArrayIterator([])));
        $items = $this->paginator->getCurrentItems();

        foreach ($items as $item) {
        }

        $this->assertEmpty($items);
    }

    /**
     * @group Laminas-8084
     */
    public function testGetItemsSerializable()
    {
        $items = $this->adapter->getItems(0, 1);
        $innerIterator = $items->getInnerIterator();
        $items = unserialize(serialize($items));
        $this->assertEquals(
            $items->getInnerIterator(),
            $innerIterator,
            'getItems has to be serializable to use caching'
        );
    }

    /**
     * @group Laminas-4151
     */
    public function testEmptySet()
    {
        $iterator = new \ArrayIterator([]);
        $this->adapter = new Adapter\Iterator($iterator);
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }
}
