<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator;

class ConfigProvider
{
    /**
     * Retrieve default laminas-paginator configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Retrieve dependency configuration for laminas-paginator.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            // Legacy Zend Framework aliases
            'aliases' => [
                \Zend\Paginator\AdapterPluginManager::class => AdapterPluginManager::class,
                \Zend\Paginator\ScrollingStylePluginManager::class => ScrollingStylePluginManager::class,
            ],
            'factories' => [
                AdapterPluginManager::class => AdapterPluginManagerFactory::class,
                ScrollingStylePluginManager::class => ScrollingStylePluginManagerFactory::class,
            ],
        ];
    }
}
