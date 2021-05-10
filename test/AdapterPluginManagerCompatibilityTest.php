<?php

namespace LaminasTest\Paginator;

use Iterator;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\Paginator\Exception\RuntimeException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function strpos;

class AdapterPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @return AdapterPluginManager
     */
    protected function getPluginManager()
    {
        return new AdapterPluginManager(new ServiceManager());
    }

    /**
     * @return string
     */
    protected function getV2InvalidPluginException()
    {
        return RuntimeException::class;
    }

    /**
     * @return string
     */
    protected function getInstanceOf()
    {
        return AdapterInterface::class;
    }

    /**
     * @return iterable
     * @psalm-return iterable<string, array{0: string, 1: string}>
     */
    public function aliasProvider()
    {
        $pluginManager = $this->getPluginManager();
        $r             = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            // Skipping as these have required arguments
            if (strpos($target, '\\Db')) {
                continue;
            }

            // Skipping as has required arguments
            if (strpos($target, '\\Callback')) {
                continue;
            }

            // Skipping as has required arguments
            if (strpos($target, Iterator::class)) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }
}
