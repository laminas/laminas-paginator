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
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\AdapterPluginManager;

/**
 * @group      Laminas_Paginator
 */
class AdapterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $adapterPluginManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
    */
    protected $mockSelect;

    protected $mockAdapter;

    protected function setUp()
    {
        $this->adapterPluginManager = new AdapterPluginManager(
            $this->getMockBuilder(ContainerInterface::class)->getMock()
        );
        $this->mockSelect = $this->getMock(Select::class);

        $mockStatement = $this->getMock(DbDriver\StatementInterface::class);
        $mockResult = $this->getMock(DbDriver\ResultInterface::class);

        $mockDriver = $this->getMock(DbDriver\DriverInterface::class);
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->getMock(Platform\PlatformInterface::class);
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
        $plugin = $this->adapterPluginManager->get('iterator', [ new ArrayIterator(range(1, 101)) ]);
        $this->assertInstanceOf(Adapter\Iterator::class, $plugin);
        $plugin = $this->adapterPluginManager->get('dbselect', [$this->mockSelect, $this->mockAdapter]);
        $this->assertInstanceOf(Adapter\DbSelect::class, $plugin);
        $plugin = $this->adapterPluginManager->get('null', [ 101 ]);
        $this->assertInstanceOf(Adapter\NullFill::class, $plugin);

        // Test dbtablegateway
        $mockStatement = $this->getMock(DbDriver\StatementInterface::class);
        $mockDriver = $this->getMock(DbDriver\DriverInterface::class);
        $mockDriver->expects($this->any())
                   ->method('createStatement')
                   ->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter = $this->getMockForAbstractClass(
            DbAdapter::class,
            [$mockDriver, new Platform\Sql92()]
        );
        $mockTableGateway = $this->getMockForAbstractClass(
            TableGateway::class,
            ['foobar', $mockAdapter]
        );
        $where  = "foo = bar";
        $order  = "foo";
        $group  = "foo";
        $having = "count(foo)>0";
        $plugin = $this->adapterPluginManager->get(
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
}
