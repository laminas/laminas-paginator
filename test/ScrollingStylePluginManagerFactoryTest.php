<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\Paginator\ScrollingStylePluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ScrollingStylePluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ScrollingStylePluginManagerFactory();

        $scrollingStyles = $factory($container, ScrollingStylePluginManager::class);
        $this->assertInstanceOf(ScrollingStylePluginManager::class, $scrollingStyles);
    }

    #[Depends('testFactoryReturnsPluginManager')]
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container      = $this->createMock(ContainerInterface::class);
        $scrollingStyle = $this->createMock(ScrollingStyleInterface::class);

        $factory         = new ScrollingStylePluginManagerFactory();
        $scrollingStyles = $factory($container, ScrollingStylePluginManager::class, [
            'services' => [
                'test' => $scrollingStyle,
            ],
        ]);
        $this->assertSame($scrollingStyle, $scrollingStyles->get('test'));
    }

    #[Depends('testFactoryReturnsPluginManager')]
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2(): void
    {
        $container      = $this->createMock(ServiceLocatorInterface::class);
        $scrollingStyle = $this->createMock(ScrollingStyleInterface::class);

        $factory = new ScrollingStylePluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $scrollingStyle,
            ],
        ]);

        $scrollingStyles = $factory->createService($container);
        $this->assertSame($scrollingStyle, $scrollingStyles->get('test'));
    }
}
