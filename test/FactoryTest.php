<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use ArrayIterator;
use Laminas\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Adapter\Iterator;
use Laminas\Paginator\Exception\InvalidArgumentException;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;
use PHPUnit\Framework\TestCase;

use function count;
use function sprintf;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\Factory<extended>
 */
class FactoryTest extends TestCase
{
    public function testCanFactoryPaginatorWithStringAdapterObject(): void
    {
        $datas     = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, new Adapter\ArrayAdapter($datas));
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterName(): void
    {
        $datas     = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, 'array');
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterAggregate(): void
    {
        $paginator = Paginator\Factory::factory(null, new TestArrayAggregate());
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithDbSelect(): void
    {
        $this->markTestSkipped(sprintf(
            '%s adapter is deprecated starting with version 2.10.x',
            DbSelect::class
        ));
        /*
        $paginator = Paginator\Factory::factory([$this->mockSelect, $this->mockAdapter], 'dbselect');
        $this->assertInstanceOf(DbSelect::class, $paginator->getAdapter());
         */
    }

    public function testCanFactoryPaginatorWithOneParameterWithArrayAdapter(): void
    {
        $datas     = [
            'items'   => [1, 2, 3],
            'adapter' => 'array',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf(ArrayAdapter::class, $paginator->getAdapter());
        $this->assertEquals(count($datas['items']), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithAdapterAcceptingOneParameter(): void
    {
        $datas     = [
            'items'   => [new ArrayIterator([])],
            'adapter' => 'iterator',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf(Iterator::class, $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneBadParameter(): void
    {
        $datas = [
            [1, 2, 3],
            'array',
        ];
        $this->expectException(InvalidArgumentException::class);
        Paginator\Factory::factory($datas);
    }
}
