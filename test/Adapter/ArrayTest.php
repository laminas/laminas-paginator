<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use PHPUnit\Framework\TestCase;

use function assert;
use function range;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Adapter\ArrayAdapter<extended>
 */
class ArrayTest extends TestCase
{
    private ?ArrayAdapter $adapter;

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

        assert($this->adapter instanceof ArrayAdapter);

        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen(): void
    {
        $expected = range(11, 20);

        assert($this->adapter instanceof ArrayAdapter);

        $actual = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount(): void
    {
        assert($this->adapter instanceof ArrayAdapter);

        $this->assertEquals(101, $this->adapter->count());
    }

    /**
     * @group Laminas-4151
     */
    public function testEmptySet(): void
    {
        $this->adapter = new Adapter\ArrayAdapter([]);
        $actual        = $this->adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }
}
