<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Select;
use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Exception\InvalidArgumentException;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Factory<extended>
 */
class FactoryTest extends TestCase
{
    /** @var MockObject|Select */
    protected $mockSelect;

    /** @var MockObject|DbAdapter */
    protected $mockAdapter;

    protected function setUp(): void
    {
        $this->mockSelect = $this->createMock(Select::class);

        $mockStatement = $this->createMock(StatementInterface::class);
        $mockResult    = $this->createMock(ResultInterface::class);

        $mockDriver = $this->createMock(DriverInterface::class);
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->createMock(PlatformInterface::class);
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockAdapter = $this->getMockForAbstractClass(
            DbAdapter::class,
            [$mockDriver, $mockPlatform]
        );
    }

    public function testCanFactoryPaginatorWithStringAdapterObject()
    {
        $datas     = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, new Adapter\ArrayAdapter($datas));
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterName()
    {
        $datas     = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, 'array');
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterAggregate()
    {
        $paginator = Paginator\Factory::factory(null, new TestArrayAggregate());
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithDbSelect()
    {
        $paginator = Paginator\Factory::factory([$this->mockSelect, $this->mockAdapter], 'dbselect');
        $this->assertInstanceOf(DbSelect::class, $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneParameterWithArrayAdapter()
    {
        $datas     = [
            'items'   => [1, 2, 3],
            'adapter' => 'array',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
        $this->assertEquals(count($datas['items']), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithOneParameterWithDbAdapter()
    {
        $datas     = [
            'items'   => [$this->mockSelect, $this->mockAdapter],
            'adapter' => 'dbselect',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf(DbSelect::class, $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneBadParameter()
    {
        $datas = [
            [1, 2, 3],
            'array',
        ];
        $this->expectException(InvalidArgumentException::class);
        $paginator = Paginator\Factory::factory($datas);
    }
}
