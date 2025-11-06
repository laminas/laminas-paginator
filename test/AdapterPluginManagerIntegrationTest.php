<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayIterator;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\Callback;
use Laminas\Paginator\Adapter\Iterator;
use Laminas\Paginator\Adapter\NullFill;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\Paginator\ConfigProvider;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

use function array_replace_recursive;
use function iterator_to_array;

final class AdapterPluginManagerIntegrationTest extends TestCase
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

    public function testArrayAdapterRetrieval(): void
    {
        $manager = $this->containerWithConfig()->get(AdapterPluginManager::class);
        $adapter = $manager->build(ArrayAdapter::class, [1, 2, 3]);
        self::assertInstanceOf(ArrayAdapter::class, $adapter);
        self::assertSame([1, 2, 3], $adapter->getItems(0, 5));
    }

    public function testCallbackAdapterRetrieval(): void
    {
        $manager = $this->containerWithConfig()->get(AdapterPluginManager::class);
        $adapter = $manager->build(Callback::class, [
            static fn (): array => [1, 2, 3],
            static fn (): int => 3,
        ]);
        self::assertInstanceOf(Callback::class, $adapter);
        self::assertSame([1, 2, 3], $adapter->getItems(0, 5));
    }

    public function testNullFillAdapterRetrieval(): void
    {
        $manager = $this->containerWithConfig()->get(AdapterPluginManager::class);
        $adapter = $manager->build(NullFill::class, [3]);
        self::assertInstanceOf(NullFill::class, $adapter);
        self::assertSame([null, null, null], $adapter->getItems(0, 5));
    }

    public function testIteratorAdapterRetrieval(): void
    {
        $manager = $this->containerWithConfig()->get(AdapterPluginManager::class);
        $adapter = $manager->build(Iterator::class, [new ArrayIterator([1, 2, 3])]);
        self::assertInstanceOf(Iterator::class, $adapter);
        self::assertSame([1, 2, 3], iterator_to_array($adapter->getItems(0, 5)));
    }
}
