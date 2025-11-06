<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayObject;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\AdapterPluginManagerFactory;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class AdapterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManagerWhenConfigIsNotAvailable(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);
        $container
            ->expects($this->never())
            ->method('get');

        $factory = new AdapterPluginManagerFactory();
        $factory($container, 'whatever');
    }

    public function testDoesNotConfigureAdditionalPaginatorsWhenConfigServiceDoesNotContainPaginatorsConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn(['foo' => 'bar']);

        $factory  = new AdapterPluginManagerFactory();
        $adapters = $factory($container, 'whatever');
        $this->assertFalse($adapters->has('foo'));
    }

    public function testConfiguresPaginatorServicesWhenFound(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);

        /** @psalm-var callable(): MockObject&AdapterInterface $factory */
        $factory = static fn(): AdapterInterface => $adapter;

        $config = [
            'paginators' => [
                'aliases'   => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => $factory,
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory       = new AdapterPluginManagerFactory();
        $pluginManager = $factory($container, 'whatever');

        $this->assertTrue($pluginManager->has('test'));
        $this->assertSame($adapter, $pluginManager->get('test'));
        $this->assertTrue($pluginManager->has('test-too'));
        $this->assertSame($adapter, $pluginManager->get('test-too'));
    }

    public function testConfigurationCanBeAnObject(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);

        $config = new ArrayObject([
            'paginators' => [
                'factories' => [
                    'foo' => static fn (): AdapterInterface => $adapter,
                ],
            ],
        ]);

        $container     = new ServiceManager([
            'services' => [
                'config' => $config,
            ],
        ]);
        $factory       = new AdapterPluginManagerFactory();
        $pluginManager = $factory($container, 'whatever');

        self::assertSame($adapter, $pluginManager->get('foo'));
    }
}
