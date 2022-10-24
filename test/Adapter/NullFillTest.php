<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use PHPUnit\Framework\TestCase;

use function array_fill;
use function assert;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Adapter\NullFill<extended>
 */
class NullFillTest extends TestCase
{
    private ?Adapter\NullFill $adapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new Adapter\NullFill(101);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testGetsItems(): void
    {
        assert($this->adapter instanceof Adapter\NullFill);

        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals(array_fill(0, 10, null), $actual);
    }

    public function testReturnsCorrectCount(): void
    {
        assert($this->adapter instanceof Adapter\NullFill);

        $this->assertEquals(101, $this->adapter->count());
    }

    /**
     * @group Laminas-3873
     */
    public function testAdapterReturnsCorrectValues(): void
    {
        $paginator = new Paginator\Paginator(new Adapter\NullFill(2));
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(5);

        $pages = $paginator->getPages();

        $this->assertEquals(2, $pages->currentItemCount);
        $this->assertEquals(2, $pages->lastItemNumber);

        $paginator = new Paginator\Paginator(new Adapter\NullFill(19));
        $paginator->setCurrentPageNumber(4);
        $paginator->setItemCountPerPage(5);

        $pages = $paginator->getPages();

        $this->assertEquals(4, $pages->currentItemCount);
        $this->assertEquals(19, $pages->lastItemNumber);
    }

    /**
     * @group Laminas-4151
     */
    public function testEmptySet(): void
    {
        $this->adapter = new Adapter\NullFill(0);
        $actual        = $this->adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }

    /**
     * Verify that the fix for Laminas-4151 doesn't create an OBO error
     */
    public function testSetOfOne(): void
    {
        $this->adapter = new Adapter\NullFill(1);
        $actual        = $this->adapter->getItems(0, 10);
        $this->assertEquals(array_fill(0, 1, null), $actual);
    }
}
