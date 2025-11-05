<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final readonly class ConfigProvider
{
    /**
     * Retrieve default laminas-paginator configuration.
     *
     * @return array{dependencies: ServiceManagerConfiguration}
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Retrieve dependency configuration for laminas-paginator.
     *
     * @return ServiceManagerConfiguration
     */
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                AdapterPluginManager::class => AdapterPluginManagerFactory::class,
            ],
        ];
    }
}
