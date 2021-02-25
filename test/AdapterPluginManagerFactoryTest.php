<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Interop\Container\ContainerInterface;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\Paginator\AdapterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdapterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
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

        $adapters = $factory($container, AdapterPluginManager::class);
        $this->assertInstanceOf(AdapterPluginManager::class, $adapters);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
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

        $adapter = $this->createMock(AdapterInterface::class);

        $factory  = new AdapterPluginManagerFactory();
        $adapters = $factory($container, AdapterPluginManager::class, [
            'services' => [
                'test' => $adapter,
            ],
        ]);
        $this->assertSame($adapter, $adapters->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);
        $container
            ->expects($this->never())
            ->method('get');

        $adapter = $this->createMock(AdapterInterface::class);

        $factory = new AdapterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $adapter,
            ],
        ]);

        $adapters = $factory->createService($container);
        $this->assertSame($adapter, $adapters->get('test'));
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
        $adapters = $factory($container, AdapterPluginManager::class);

        $this->assertInstanceOf(AdapterPluginManager::class, $adapters);
        $this->assertFalse($adapters->has('foo'));
    }

    public function testConfiguresPaginatorServicesWhenFound(): void
    {
        $paginator = $this->createMock(AdapterInterface::class);

        /** @psalm-var callable(ContainerInterface ): MockObject&AdapterInterface $factory */
        $factory = function (ContainerInterface $container) use ($paginator): AdapterInterface {
            return $paginator;
        };

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

        $factory    = new AdapterPluginManagerFactory();
        $paginators = $factory($container, AdapterPluginManager::class);

        $this->assertInstanceOf(AdapterPluginManager::class, $paginators);
        $this->assertTrue($paginators->has('test'));
        $this->assertSame($paginator, $paginators->get('test'));
        $this->assertTrue($paginators->has('test-too'));
        $this->assertSame($paginator, $paginators->get('test-too'));
    }
}
