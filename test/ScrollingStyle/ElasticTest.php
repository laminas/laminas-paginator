<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\ScrollingStyle;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\Elastic;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function count;
use function range;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\ScrollingStyle\Elastic<extended>
 */
class ElasticTest extends TestCase
{
    /** @var Elastic */
    private $scrollingStyle;
    /** @var Paginator */
    private $paginator;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->scrollingStyle = new Elastic();
        $this->paginator      = new Paginator(new ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(5);
        $this->paginator->setPageRange(5);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->scrollingStyle = null;
        $this->paginator      = null;
        parent::tearDown();
    }

    public function testGetsPagesInRangeForFirstPage(): void
    {
        $this->paginator->setCurrentPageNumber(1);
        $actual   = $this->scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(1, 5), range(1, 5));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesInRangeForSecondPage(): void
    {
        $this->paginator->setCurrentPageNumber(2);
        $actual   = $this->scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(1, 6), range(1, 6));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesInRangeForTenthPage(): void
    {
        $this->paginator->setCurrentPageNumber(10);
        $actual   = $this->scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(6, 14), range(6, 14));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesInRangeForLastPage(): void
    {
        $this->paginator->setCurrentPageNumber(21);
        $actual   = $this->scrollingStyle->getPages($this->paginator);
        $expected = array_combine(range(17, 21), range(17, 21));
        $this->assertEquals($expected, $actual);
    }

    public function testGetsNextAndPreviousPageForFirstPage(): void
    {
        $this->paginator->setCurrentPageNumber(1);
        $pages = $this->paginator->getPages('Elastic');

        $this->assertEquals(2, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondPage(): void
    {
        $this->paginator->setCurrentPageNumber(2);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(1, $pages->previous);
        $this->assertEquals(3, $pages->next);
    }

    public function testGetsNextAndPreviousPageForMiddlePage(): void
    {
        $this->paginator->setCurrentPageNumber(10);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(9, $pages->previous);
        $this->assertEquals(11, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondLastPage(): void
    {
        $this->paginator->setCurrentPageNumber(20);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(19, $pages->previous);
        $this->assertEquals(21, $pages->next);
    }

    public function testGetsNextAndPreviousPageForLastPage(): void
    {
        $this->paginator->setCurrentPageNumber(21);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(20, $pages->previous);
    }

    public function testNoPagesOnLastPageEqualsPageRange(): void
    {
        $this->paginator->setPageRange(3);
        $this->paginator->setCurrentPageNumber(21);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(3, count($pages->pagesInRange));
    }

    public function testNoPagesOnSecondLastPageEqualsPageRangeMinOne(): void
    {
        $this->paginator->setPageRange(3);
        $this->paginator->setCurrentPageNumber(20);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(4, count($pages->pagesInRange));
    }

    public function testNoPagesBeforeSecondLastPageEqualsPageRangeMinTwo(): void
    {
        $this->paginator->setPageRange(3);
        $this->paginator->setCurrentPageNumber(19);
        $pages = $this->paginator->getPages('Elastic');
        $this->assertEquals(5, count($pages->pagesInRange));
    }
}
