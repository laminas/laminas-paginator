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
use LaminasTest\Paginator\TestAsset\ServiceLocator;
use PHPUnit\Framework\TestCase;

class AdapterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
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
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
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
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->createMock(ServiceLocator::class);
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

    public function testDoesNotConfigureAdditionalPaginatorsWhenConfigServiceDoesNotContainPaginatorsConfig()
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

    public function testConfiguresPaginatorServicesWhenFound()
    {
        $paginator = $this->createMock(AdapterInterface::class);
        $config    = [
            'paginators' => [
                'aliases'   => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($paginator) {
                        return $paginator;
                    },
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
