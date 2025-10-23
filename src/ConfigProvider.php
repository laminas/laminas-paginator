<?php

declare(strict_types=1);

namespace Laminas\Paginator;

/** @final */
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
            'factories' => [
                AdapterPluginManager::class        => AdapterPluginManagerFactory::class,
                ScrollingStylePluginManager::class => ScrollingStylePluginManagerFactory::class,
            ],
        ];
    }
}
