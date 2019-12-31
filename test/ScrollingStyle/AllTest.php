<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\ScrollingStyle;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\ScrollingStyle\All<extended>
 */
class AllTest extends TestCase
{
    /**
     * @var \Laminas\Paginator\ScrollingStyle\All
     */
    private $scrollingStyle = null;

    /**
     * @var \Laminas\Paginator\Paginator
     */
    private $paginator = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->scrollingStyle = new \Laminas\Paginator\ScrollingStyle\All();
        $this->paginator = new Paginator(new ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(10);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->scrollingStyle = null;
        $this->paginator = null;
        parent::tearDown();
    }

    public function testGetsPages()
    {
        $expected = array_combine(range(1, 11), range(1, 11));
        $pages = $this->scrollingStyle->getPages($this->paginator);
        $this->assertEquals($expected, $pages);
    }

    public function testGetsNextAndPreviousPageForFirstPage()
    {
        $this->paginator->setCurrentPageNumber(1);
        $pages = $this->paginator->getPages('All');

        $this->assertEquals(2, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondPage()
    {
        $this->paginator->setCurrentPageNumber(2);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(1, $pages->previous);
        $this->assertEquals(3, $pages->next);
    }

    public function testGetsNextAndPreviousPageForMiddlePage()
    {
        $this->paginator->setCurrentPageNumber(6);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(5, $pages->previous);
        $this->assertEquals(7, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondLastPage()
    {
        $this->paginator->setCurrentPageNumber(10);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(9, $pages->previous);
        $this->assertEquals(11, $pages->next);
    }

    public function testGetsNextAndPreviousPageForLastPage()
    {
        $this->paginator->setCurrentPageNumber(11);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(10, $pages->previous);
    }
}
