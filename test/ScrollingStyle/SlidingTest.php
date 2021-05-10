<?php

namespace LaminasTest\Paginator\ScrollingStyle;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\Sliding;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function range;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\ScrollingStyle\Sliding<extended>
 */
class SlidingTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * @var \Laminas\Paginator\ScrollingStyle\Sliding
     */
    private $_scrollingStyle;
    // @codingStandardsIgnoreEnd

    /** @var Paginator */
    private $paginator;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_scrollingStyle = new Sliding();
        $this->paginator       = new Paginator(new ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(10);
        $this->paginator->setPageRange(5);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->_scrollingStyle = null;
        $this->paginator       = null;
        parent::tearDown();
    }

    public function testGetsPagesInRangeForFirstPage(): void
    {
        $this->paginator->setCurrentPageNumber(1);
        $actual   = $this->_scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(1, 5), range(1, 5));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesInRangeForSecondPage(): void
    {
        $this->paginator->setCurrentPageNumber(2);
        $actual   = $this->_scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(1, 5), range(1, 5));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesInRangeForFifthPage(): void
    {
        $this->paginator->setCurrentPageNumber(5);
        $actual   = $this->_scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(3, 7), range(3, 7));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesInRangeForLastPage(): void
    {
        $this->paginator->setCurrentPageNumber(11);
        $actual   = $this->_scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(7, 11), range(7, 11));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsNextAndPreviousPageForFirstPage(): void
    {
        $this->paginator->setCurrentPageNumber(1);
        $pages = $this->paginator->getPages('Sliding');

        $this->assertEquals(2, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondPage(): void
    {
        $this->paginator->setCurrentPageNumber(2);
        $pages = $this->paginator->getPages('Sliding');
        $this->assertEquals(1, $pages->previous);
        $this->assertEquals(3, $pages->next);
    }

    public function testGetsNextAndPreviousPageForMiddlePage(): void
    {
        $this->paginator->setCurrentPageNumber(6);
        $pages = $this->paginator->getPages('Sliding');
        $this->assertEquals(5, $pages->previous);
        $this->assertEquals(7, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondLastPage(): void
    {
        $this->paginator->setCurrentPageNumber(10);
        $pages = $this->paginator->getPages('Sliding');
        $this->assertEquals(9, $pages->previous);
        $this->assertEquals(11, $pages->next);
    }

    public function testGetsNextAndPreviousPageForLastPage(): void
    {
        $this->paginator->setCurrentPageNumber(11);
        $pages = $this->paginator->getPages('Sliding');
        $this->assertEquals(10, $pages->previous);
    }

    public function testAcceptsPageRangeLargerThanPageCount(): void
    {
        $this->paginator->setPageRange(100);
        $pages = $this->paginator->getPages();
        $this->assertEquals(11, $pages->last);
    }
}
