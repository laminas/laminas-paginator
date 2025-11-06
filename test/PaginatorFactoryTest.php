<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayIterator;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\ConfigProvider;
use Laminas\Paginator\PaginatorFactoryFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Paginator\TestAsset\TestArrayAggregate;
use PHPUnit\Framework\TestCase;

use function array_replace_recursive;
use function iterator_to_array;
use function range;

final class PaginatorFactoryTest extends TestCase
{
    private function containerWithConfig(array $config = []): ServiceManager
    {
        $config = array_replace_recursive(
            (new ConfigProvider())->__invoke(),
            $config,
        );

        /** @psalm-suppress MixedArrayAssignment Life is too short */
        $config['dependencies']['services']['config'] = $config;

        /** @psalm-suppress MixedArgument */
        return new ServiceManager($config['dependencies']);
    }

    public function testWithAdapterAppliesDefaults(): void
    {
        $container = $this->containerWithConfig([
            'paginator' => [
                'itemCountPerPage' => 3,
                'pageRange'        => 3,
                'scrollingStyle'   => 'jumping',
            ],
        ]);

        $factory = (new PaginatorFactoryFactory())->__invoke($container, 'foo');

        $pager = $factory->withAdapter(new ArrayAdapter(range(1, 100)));

        self::assertSame(3, $pager->getItemCountPerPage());
        self::assertSame(3, $pager->getPageRange());
        $pager->setCurrentPageNumber(5);

        self::assertSame([
            4 => 4,
            5 => 5,
            6 => 6,
        ], $pager->getPages()->pagesInRange);
    }

    public function testAdaptersCanBeBuilt(): void
    {
        $container = $this->containerWithConfig([
            'paginator' => [
                'itemCountPerPage' => 3,
                'pageRange'        => 3,
                'scrollingStyle'   => 'jumping',
            ],
        ]);

        $factory = (new PaginatorFactoryFactory())->__invoke($container, 'foo');

        $pager = $factory->buildAdapter('array', range(1, 100));

        self::assertSame(3, $pager->getItemCountPerPage());
        self::assertSame(3, $pager->getPageRange());
        $pager->setCurrentPageNumber(5);

        self::assertSame([
            4 => 4,
            5 => 5,
            6 => 6,
        ], $pager->getPages()->pagesInRange);
    }

    public function testBuildAdapterAcceptsAggregate(): void
    {
        $container = $this->containerWithConfig([
            'paginator'  => [
                'itemCountPerPage' => 3,
                'pageRange'        => 3,
                'scrollingStyle'   => 'jumping',
            ],
            'paginators' => [
                'factories' => [
                    TestArrayAggregate::class => InvokableFactory::class,
                ],
            ],
        ]);

        $factory = (new PaginatorFactoryFactory())->__invoke($container, 'foo');

        $pager = $factory->buildAdapter(TestArrayAggregate::class);

        self::assertSame(3, $pager->getItemCountPerPage());
        self::assertSame(3, $pager->getPageRange());
        $pager->setCurrentPageNumber(1);

        self::assertSame([
            1 => 1,
            2 => 2,
        ], $pager->getPages()->pagesInRange);

        self::assertSame([1, 2, 3], iterator_to_array($pager->getCurrentItems()));
    }

    public function testCreationWithItemArray(): void
    {
        $container = $this->containerWithConfig([
            'paginator' => [
                'itemCountPerPage' => 3,
                'pageRange'        => 3,
                'scrollingStyle'   => 'jumping',
            ],
        ]);

        $factory = (new PaginatorFactoryFactory())->__invoke($container, 'foo');

        $pager = $factory->withItems(range(1, 100));

        self::assertSame(3, $pager->getItemCountPerPage());
        self::assertSame(3, $pager->getPageRange());
        $pager->setCurrentPageNumber(5);

        self::assertSame([
            4 => 4,
            5 => 5,
            6 => 6,
        ], $pager->getPages()->pagesInRange);

        self::assertEquals([13, 14, 15], iterator_to_array($pager->getCurrentItems()));
    }

    public function testCreationWithIterable(): void
    {
        $container = $this->containerWithConfig([
            'paginator' => [
                'itemCountPerPage' => 3,
                'pageRange'        => 3,
                'scrollingStyle'   => 'jumping',
            ],
        ]);

        $factory = (new PaginatorFactoryFactory())->__invoke($container, 'foo');

        $pager = $factory->withItems(new ArrayIterator(range(1, 100)));

        self::assertSame(3, $pager->getItemCountPerPage());
        self::assertSame(3, $pager->getPageRange());
        $pager->setCurrentPageNumber(5);

        self::assertSame([
            4 => 4,
            5 => 5,
            6 => 6,
        ], $pager->getPages()->pagesInRange);

        self::assertEquals([13, 14, 15], iterator_to_array($pager->getCurrentItems()));
    }
}
