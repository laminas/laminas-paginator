<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter\DbSelect;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Adapter\DbSelect<extended>
 */
class DbSelectTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\Db\Sql\Select */
    protected $mockSelect;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\Db\Sql\Select */
    protected $mockSelectCount;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\Db\Adapter\Driver\StatementInterface */
    protected $mockStatement;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\Db\Adapter\Driver\ResultInterface */
    protected $mockResult;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Laminas\Db\Sql\Sql */
    protected $mockSql;

    /** @var DbSelect */
    protected $dbSelect;

    public function setUp()
    {
        $this->mockResult    = $this->createMock('Laminas\Db\Adapter\Driver\ResultInterface');
        $this->mockStatement = $this->createMock('Laminas\Db\Adapter\Driver\StatementInterface');

        $this->mockStatement->expects($this->any())->method('execute')->will($this->returnValue($this->mockResult));

        $mockDriver   = $this->createMock('Laminas\Db\Adapter\Driver\DriverInterface');
        $mockPlatform = $this->createMock('Laminas\Db\Adapter\Platform\PlatformInterface');

        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($this->mockStatement));
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockSql = $this->getMockBuilder('Laminas\Db\Sql\Sql')
            ->setMethods(['prepareStatementForSqlObject', 'execute'])
            ->setConstructorArgs(
                [
                    $this->getMockForAbstractClass(
                        'Laminas\Db\Adapter\Adapter',
                        [$mockDriver, $mockPlatform]
                    )
                ]
            )->getMock();

        $this
            ->mockSql
            ->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->with($this->isInstanceOf('Laminas\Db\Sql\Select'))
            ->will($this->returnValue($this->mockStatement));

        $this->mockSelect      = $this->createMock('Laminas\Db\Sql\Select');
        $this->mockSelectCount = $this->createMock('Laminas\Db\Sql\Select');
        $this->dbSelect        = new DbSelect($this->mockSelect, $this->mockSql);
    }

    public function testGetItems()
    {
        $this->mockSelect->expects($this->once())->method('limit')->with($this->equalTo(10));
        $this->mockSelect->expects($this->once())->method('offset')->with($this->equalTo(2));
        $items = $this->dbSelect->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testCount()
    {
        $this->mockResult->expects($this->once())->method('current')
            ->will($this->returnValue([DbSelect::ROW_COUNT_COLUMN_NAME => 5]));

        $this->mockSelect->expects($this->exactly(3))->method('reset'); // called for columns, limit, offset, order

        $count = $this->dbSelect->count();
        $this->assertEquals(5, $count);
    }

    public function testCustomCount()
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql, null, $this->mockSelectCount);
        $this->mockResult->expects($this->once())->method('current')
            ->will($this->returnValue([DbSelect::ROW_COUNT_COLUMN_NAME => 7]));

        $count = $this->dbSelect->count();
        $this->assertEquals(7, $count);
    }

    /**
     * @group 6817
     * @group 6812
     */
    public function testReturnValueIsArray()
    {
        $this->assertInternalType('array', $this->dbSelect->getItems(0, 10));
    }

    public function testGetArrayCopyShouldContainSelectItems()
    {
        $this->dbSelect = new DbSelect(
            $this->mockSelect,
            $this->mockSql,
            null,
            $this->mockSelectCount
        );
        $this->assertSame(
            [
                'select',
                'count_select',
            ],
            array_keys($this->dbSelect->getArrayCopy())
        );
    }
}
