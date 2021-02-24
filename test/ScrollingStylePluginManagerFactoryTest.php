<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Interop\Container\ContainerInterface;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\Paginator\ScrollingStylePluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class ScrollingStylePluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ScrollingStylePluginManagerFactory();

        $scrollingStyles = $factory($container, ScrollingStylePluginManager::class);
        $this->assertInstanceOf(ScrollingStylePluginManager::class, $scrollingStyles);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
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

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
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
