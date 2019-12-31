<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Paginator\Adapter;

use Zend\Paginator\Adapter;

/**
 * @group      Zend_Paginator
 */
class ArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend\Paginator\Adapter\Array
     */
    private $adapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->adapter = new Adapter\ArrayAdapter(range(1, 101));
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testGetsItemsAtOffsetZero()
    {
        $expected = range(1, 10);
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen()
    {
        $expected = range(11, 20);
        $actual = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount()
    {
        $this->assertEquals(101, $this->adapter->count());
    }


    /**
     * @group ZF-4151
     */
    public function testEmptySet()
    {
        $this->adapter = new Adapter\ArrayAdapter(array());
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals(array(), $actual);
    }
}
