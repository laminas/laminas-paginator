<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Factory<extended>
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockSelect;

    protected $mockAdapter;

    protected function setUp()
    {
        $this->mockSelect = $this->getMock('Laminas\Db\Sql\Select');

        $mockStatement = $this->getMock('Laminas\Db\Adapter\Driver\StatementInterface');
        $mockResult = $this->getMock('Laminas\Db\Adapter\Driver\ResultInterface');

        $mockDriver = $this->getMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->getMock('Laminas\Db\Adapter\Platform\PlatformInterface');
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockAdapter = $this->getMockForAbstractClass(
            'Laminas\Db\Adapter\Adapter',
            [$mockDriver, $mockPlatform]
        );
    }

    public function testCanFactoryPaginatorWithStringAdapterObject()
    {
        $datas = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, new Adapter\ArrayAdapter($datas));
        $this->assertInstanceOf('Laminas\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterName()
    {
        $datas = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, 'array');
        $this->assertInstanceOf('Laminas\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterAggregate()
    {
        $paginator = Paginator\Factory::factory(null, new TestArrayAggregate);
        $this->assertInstanceOf('Laminas\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithDbSelect()
    {
        $paginator = Paginator\Factory::factory([$this->mockSelect, $this->mockAdapter], 'dbselect');
        $this->assertInstanceOf('Laminas\Paginator\Adapter\DbSelect', $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneParameterWithArrayAdapter()
    {
        $datas = [
            'items' => [1, 2, 3],
            'adapter' => 'array',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf('Laminas\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
        $this->assertEquals(count($datas['items']), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithOneParameterWithDbAdapter()
    {
        $datas = [
            'items' => [$this->mockSelect, $this->mockAdapter],
            'adapter' => 'dbselect',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf('Laminas\Paginator\Adapter\DbSelect', $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneBadParameter()
    {
        $datas = [
            [1, 2, 3],
            'array',
        ];
        $this->setExpectedException('Laminas\Paginator\Exception\InvalidArgumentException');
        $paginator = Paginator\Factory::factory($datas);
    }
}
