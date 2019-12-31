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
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ScrollingStylePluginManagerFactory();

        $scrollingStyles = $factory($container, ScrollingStylePluginManager::class);
        $this->assertInstanceOf(ScrollingStylePluginManager::class, $scrollingStyles);

        if (method_exists($scrollingStyles, 'configure')) {
            // laminas-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $scrollingStyles);
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $scrollingStyles->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $scrollingStyle = $this->prophesize(ScrollingStyleInterface::class)->reveal();

        $factory = new ScrollingStylePluginManagerFactory();
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
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $scrollingStyle = $this->prophesize(ScrollingStyleInterface::class)->reveal();

        $factory = new ScrollingStylePluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $scrollingStyle,
            ],
        ]);

        $scrollingStyles = $factory->createService($container->reveal());
        $this->assertSame($scrollingStyle, $scrollingStyles->get('test'));
    }
}
