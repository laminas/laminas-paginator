<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Adapter\Exception\MissingRowCountColumnException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

use function array_keys;
use function strtolower;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Adapter\DbSelect<extended>
 */
class DbSelectTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Select */
    protected $mockSelect;

    /** @var PHPUnit_Framework_MockObject_MockObject|Select */
    protected $mockSelectCount;

    /** @var PHPUnit_Framework_MockObject_MockObject|StatementInterface */
    protected $mockStatement;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResultInterface */
    protected $mockResult;

    /** @var PHPUnit_Framework_MockObject_MockObject|Sql */
    protected $mockSql;

    /** @var DbSelect */
    protected $dbSelect;

    public function setUp(): void
    {
        $this->mockResult    = $this->createMock(ResultInterface::class);
        $this->mockStatement = $this->createMock(StatementInterface::class);

        $this->mockStatement->expects($this->any())->method('execute')->will($this->returnValue($this->mockResult));

        $mockDriver   = $this->createMock(DriverInterface::class);
        $mockPlatform = $this->createMock(PlatformInterface::class);

        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($this->mockStatement));
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockSql = $this->getMockBuilder(Sql::class)
            ->setMethods(['prepareStatementForSqlObject', 'execute'])
            ->setConstructorArgs(
                [
                    $this->getMockForAbstractClass(
                        Adapter::class,
                        [$mockDriver, $mockPlatform]
                    ),
                ]
            )->getMock();

        $this
            ->mockSql
            ->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->with($this->isInstanceOf(Select::class))
            ->will($this->returnValue($this->mockStatement));

        $this->mockSelect      = $this->createMock(Select::class);
        $this->mockSelectCount = $this->createMock(Select::class);
        $this->dbSelect        = new DbSelect($this->mockSelect, $this->mockSql);
    }

    public function testGetItems(): void
    {
        $this->mockSelect->expects($this->once())->method('limit')->with($this->equalTo(10));
        $this->mockSelect->expects($this->once())->method('offset')->with($this->equalTo(2));
        $items = $this->dbSelect->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testCount(): void
    {
        $this->mockResult->expects($this->once())->method('current')
            ->will($this->returnValue([DbSelect::ROW_COUNT_COLUMN_NAME => 5]));

        $this->mockSelect->expects($this->exactly(3))->method('reset'); // called for columns, limit, offset, order

        $count = $this->dbSelect->count();
        $this->assertEquals(5, $count);
    }

    public function testCountQueryWithLowerColumnNameShouldReturnValidResult(): void
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql);
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->will($this->returnValue([strtolower(DbSelect::ROW_COUNT_COLUMN_NAME) => 7]));

        $count = $this->dbSelect->count();
        $this->assertEquals(7, $count);
    }

    public function testCountQueryWithMissingColumnNameShouldRaiseException(): void
    {
        $this->dbSelect = new DbSelect($this->mockSelect, $this->mockSql);
        $this->mockResult
            ->expects($this->once())
            ->method('current')
            ->will($this->returnValue([]));

        $this->expectException(MissingRowCountColumnException::class);
        $this->dbSelect->count();
    }

    public function testCustomCount(): void
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
     *
     * @return void
     */
    public function testReturnValueIsArray(): void
    {
        $this->assertIsArray($this->dbSelect->getItems(0, 10));
    }

    public function testGetArrayCopyShouldContainSelectItems(): void
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
