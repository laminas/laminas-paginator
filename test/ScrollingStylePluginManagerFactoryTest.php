<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\Paginator\ScrollingStylePluginManagerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ScrollingStylePluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ScrollingStylePluginManagerFactory();

        $scrollingStyles = $factory($container, $factory::class);
        $this->assertInstanceOf(ScrollingStylePluginManager::class, $scrollingStyles);
    }

    public function testOptionsWillBeUsedToSeedServices(): void
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
}
