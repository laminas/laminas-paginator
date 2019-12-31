<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Laminas\Db\Adapter\Platform\Sql92;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\CallbackHandler;

/**
 * @group      Laminas_Paginator
 */
class AdapterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $adapaterPluginManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockSelect;

    protected $mockAdapter;

    protected function setUp()
    {
        $this->adapaterPluginManager = new AdapterPluginManager();
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
            array($mockDriver, $mockPlatform)
        );
    }

    public function testCanRetrieveAdapterPlugin()
    {
        $plugin = $this->adapaterPluginManager->get('array', array(1, 2, 3));
        $this->assertInstanceOf('Laminas\Paginator\Adapter\ArrayAdapter', $plugin);
        $plugin = $this->adapaterPluginManager->get('iterator', new \ArrayIterator(range(1, 101)));
        $this->assertInstanceOf('Laminas\Paginator\Adapter\Iterator', $plugin);
        $plugin = $this->adapaterPluginManager->get('dbselect', array($this->mockSelect, $this->mockAdapter));
        $this->assertInstanceOf('Laminas\Paginator\Adapter\DbSelect', $plugin);
        $plugin = $this->adapaterPluginManager->get('null', 101);
        $this->assertInstanceOf('Laminas\Paginator\Adapter\NullFill', $plugin);

        //test dbtablegateway
        $mockStatement = $this->getMock('Laminas\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())
                   ->method('createStatement')
                   ->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter = $this->getMockForAbstractClass(
            'Laminas\Db\Adapter\Adapter',
            array($mockDriver, new Sql92())
        );
        $mockTableGateway = $this->getMockForAbstractClass(
            'Laminas\Db\TableGateway\TableGateway',
            array('foobar', $mockAdapter)
        );
        $where  = "foo = bar";
        $order  = "foo";
        $group  = "foo";
        $having = "count(foo)>0";
        $plugin = $this->adapaterPluginManager->get(
            'dbtablegateway',
            array($mockTableGateway, $where, $order, $group, $having)
        );
        $this->assertInstanceOf('Laminas\Paginator\Adapter\DbTableGateway', $plugin);

        //test callback
        $itemsCallback = new CallbackHandler(function () {
            return array();
        });
        $countCallback = new CallbackHandler(function () {
            return 0;
        });
        $plugin = $this->adapaterPluginManager->get('callback', array($itemsCallback, $countCallback));
        $this->assertInstanceOf('Laminas\Paginator\Adapter\Callback', $plugin);
    }

    public function testCanRetrievePluginManagerWithServiceManager()
    {
        $sm = $this->serviceManager = new ServiceManager(
            new ServiceManagerConfig(array(
                'factories' => array(
                    'PaginatorPluginManager'  => 'Laminas\Mvc\Service\PaginatorPluginManagerFactory',
                ),
            ))
        );
        $sm->setService('Config', array());
        $adapterPluginManager = $sm->get('PaginatorPluginManager');
        $this->assertInstanceOf('Laminas\Paginator\AdapterPluginManager', $adapterPluginManager);
    }
}
