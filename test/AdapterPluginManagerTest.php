<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use ArrayIterator;
use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Db\Adapter\Driver as DbDriver;
use Laminas\Db\Adapter\Platform;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\AdapterPluginManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function range;

/**
 * @covers  Laminas\Paginator\AdapterPluginManager<extended>
 */
class AdapterPluginManagerTest extends TestCase
{
    /** @var AdapterPluginManager */
    protected $adapterPluginManager;

    /** @var MockObject|Select */
    protected $mockSelect;

    /** @var MockObject|DbAdapter */
    protected $mockAdapter;

    protected function setUp(): void
    {
        $this->adapterPluginManager = new AdapterPluginManager(
            $this->getMockBuilder(ContainerInterface::class)->getMock()
        );
        $this->mockSelect           = $this->createMock(Select::class);

        $mockStatement = $this->createMock(DbDriver\StatementInterface::class);
        $mockResult    = $this->createMock(DbDriver\ResultInterface::class);

        $mockDriver = $this->createMock(DbDriver\DriverInterface::class);
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->createMock(Platform\PlatformInterface::class);
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockAdapter = $this->getMockForAbstractClass(
            DbAdapter::class,
            [$mockDriver, $mockPlatform]
        );
    }

    public function testCanRetrieveAdapterPlugin()
    {
        $plugin = $this->adapterPluginManager->get('array', [1, 2, 3]);
        $this->assertInstanceOf(Adapter\ArrayAdapter::class, $plugin);
        $plugin = $this->adapterPluginManager->get('iterator', [new ArrayIterator(range(1, 101))]);
        $this->assertInstanceOf(Adapter\Iterator::class, $plugin);
        $plugin = $this->adapterPluginManager->get('dbselect', [$this->mockSelect, $this->mockAdapter]);
        $this->assertInstanceOf(Adapter\DbSelect::class, $plugin);
        $plugin = $this->adapterPluginManager->get('null', [101]);
        $this->assertInstanceOf(Adapter\NullFill::class, $plugin);

        // Test dbtablegateway
        $mockStatement = $this->createMock(DbDriver\StatementInterface::class);
        $mockDriver    = $this->createMock(DbDriver\DriverInterface::class);
        $mockDriver->expects($this->any())
                   ->method('createStatement')
                   ->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter      = $this->getMockForAbstractClass(
            DbAdapter::class,
            [$mockDriver, new Platform\Sql92()]
        );
        $mockTableGateway = $this->getMockForAbstractClass(
            TableGateway::class,
            ['foobar', $mockAdapter]
        );
        $where            = "foo = bar";
        $order            = "foo";
        $group            = "foo";
        $having           = "count(foo)>0";
        $plugin           = $this->adapterPluginManager->get(
            'dbtablegateway',
            [$mockTableGateway, $where, $order, $group, $having]
        );
        $this->assertInstanceOf(Adapter\DbTableGateway::class, $plugin);

        // Test Callback
        $itemsCallback = function () {
            return [];
        };
        $countCallback = function () {
            return 0;
        };

        $plugin = $this->adapterPluginManager->get('callback', [$itemsCallback, $countCallback]);
        $this->assertInstanceOf(Adapter\Callback::class, $plugin);
    }

    public function testFactoryCreatedDbSelectCanUseCustomCountSelect()
    {
        $mockSelect      = $this->createMock(Select::class);
        $mockSelectCount = $this->createMock(Select::class);

        $mockResult    = $this->createMock(DbDriver\ResultInterface::class);
        $mockStatement = $this->createMock(DbDriver\StatementInterface::class);

        $mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $mockSql = $this->getMockBuilder(Sql::class)
            ->setMethods(['prepareStatementForSqlObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSql
            ->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->with($mockSelectCount)
            ->will($this->returnValue($mockStatement));

        $mockResult
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue([Adapter\DbSelect::ROW_COUNT_COLUMN_NAME => 5]));

        $plugin = $this->adapterPluginManager->get(
            'dbselect',
            [$mockSelect, $mockSql, null, $mockSelectCount]
        );
        $count  = $plugin->count();
        $this->assertEquals(5, $count);
    }
}
