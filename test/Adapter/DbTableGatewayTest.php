<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\Sql92;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Adapter\DbTableGateway;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

use function sprintf;

/**
 * @covers  Laminas\Paginator\Adapter\DbTableGateway<extended>
 */
class DbTableGatewayTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $mockStatement;

    /** @var DbTableGateway */
    protected $dbTableGateway;

    /** @var TableGateway */
    protected $mockTableGateway;

    public function setup(): void
    {
        $this->markTestSkipped(sprintf(
            'Tests for %s adapter are skipped because it is deprecated.',
            DbTableGateway::class
        ));

        $mockStatement = $this->createMock(StatementInterface::class);
        $mockDriver    = $this->createMock(DriverInterface::class);
        $mockDriver->expects($this->any())
                   ->method('createStatement')
                   ->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter = $this->getMockForAbstractClass(
            Adapter::class,
            [$mockDriver, new Sql92()]
        );

        $tableName        = 'foobar';
        $mockTableGateway = $this->getMockForAbstractClass(
            TableGateway::class,
            [$tableName, $mockAdapter]
        );

        $this->mockStatement = $mockStatement;

        $this->mockTableGateway = $mockTableGateway;
    }

    public function testGetItems(): void
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
             ->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testCount(): void
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $mockResult->expects($this->any())
                   ->method('current')
                   ->will($this->returnValue([DbSelect::ROW_COUNT_COLUMN_NAME => 10]));

        $this->mockStatement->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $count = $this->dbTableGateway->count();
        $this->assertEquals(10, $count);
    }

    public function testGetItemsWithWhereAndOrder(): void
    {
        $where                = "foo = bar";
        $order                = "foo";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
             ->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testGetItemsWithWhereAndOrderAndGroup(): void
    {
        $where                = "foo = bar";
        $order                = "foo";
        $group                = "foo";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            // @codingStandardsIgnoreStart
            ->with($this->equalTo('SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" ORDER BY "foo" ASC LIMIT limit OFFSET offset'));
            // @codingStandardsIgnoreEnd
        $this->mockStatement
             ->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testGetItemsWithWhereAndOrderAndGroupAndHaving(): void
    {
        $where                = "foo = bar";
        $order                = "foo";
        $group                = "foo";
        $having               = "count(foo)>0";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group, $having);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            // @codingStandardsIgnoreStart
            ->with($this->equalTo('SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" HAVING count(foo)>0 ORDER BY "foo" ASC LIMIT limit OFFSET offset'));
            // @codingStandardsIgnoreEnd
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }
}
