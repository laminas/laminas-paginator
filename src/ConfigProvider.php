<?php

declare(strict_types=1);

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
            'aliases'   => [
                \Zend\Paginator\AdapterPluginManager::class        => AdapterPluginManager::class,
                \Zend\Paginator\ScrollingStylePluginManager::class => ScrollingStylePluginManager::class,
            ],
            'factories' => [
                AdapterPluginManager::class        => AdapterPluginManagerFactory::class,
                ScrollingStylePluginManager::class => ScrollingStylePluginManagerFactory::class,
            ],
        ];
    }
}
