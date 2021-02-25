<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Adapter\ArrayAdapter<extended>
 */
class ArrayTest extends TestCase
{
    /** @var Adapter\ArrayAdapter */
    private $adapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new Adapter\ArrayAdapter(range(1, 101));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testGetsItemsAtOffsetZero(): void
    {
        $expected = range(1, 10);
        $actual   = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen(): void
    {
        $expected = range(11, 20);
        $actual   = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount(): void
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    /**
     * @group Laminas-4151
     *
     * @return void
     */
    public function testEmptySet(): void
    {
        $this->adapter = new Adapter\ArrayAdapter([]);
        $actual        = $this->adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }
}
