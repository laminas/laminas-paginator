<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Paginator\Adapter\DbSelect;

/**
 * @category   Laminas
 * @package    Laminas_Paginator
 * @subpackage UnitTests
 * @group      Laminas_Paginator
 */
class DbSelectTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockSelect;

    protected $mockResult;

    /** @var DbSelect */
    protected $dbSelect;

    public function setup()
    {
        $mockStatement = $this->getMock('Laminas\Db\Adapter\Driver\StatementInterface');
        $mockResult = $this->getMock('Laminas\Db\Adapter\Driver\ResultInterface');

        $mockDriver = $this->getMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->getMock('Laminas\Db\Adapter\Platform\PlatformInterface');
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));
        $mockAdapter = $this->getMockForAbstractClass(
            'Laminas\Db\Adapter\Adapter',
            array($mockDriver, $mockPlatform)
        );

        $this->mockSelect = $this->getMock('Laminas\Db\Sql\Select');
        $this->mockResult = $mockResult;
        $this->dbSelect = new DbSelect($this->mockSelect, $mockAdapter);
    }

    public function testGetItems()
    {
        $this->mockSelect->expects($this->once())->method('limit')->with($this->equalTo(10));
        $this->mockSelect->expects($this->once())->method('offset')->with($this->equalTo(2));
        $items = $this->dbSelect->getItems(2, 10);
        $this->assertInstanceOf('Laminas\Db\ResultSet\ResultSet', $items);
    }

    public function testCount()
    {
        $this->mockSelect->expects($this->once())->method('columns')->with($this->equalTo(array('c' => new Expression('COUNT(1)'))));
        $this->mockResult->expects($this->any())->method('current')->will($this->returnValue(array('c' => 5)));
        $count = $this->dbSelect->count();
        $this->assertEquals(5, $count);
    }
}
