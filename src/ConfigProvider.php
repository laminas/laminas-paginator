<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type PaginatorDefaults = array{
 *     itemCountPerPage: int<1, max>,
 *     pageRange: int<1, max>,
 *     scrollingStyle: string|ScrollingStyleInterface,
 * }
 */
final readonly class ConfigProvider
{
    /**
     * Retrieve default laminas-paginator configuration.
     *
     * @return array{
     *     dependencies: ServiceManagerConfiguration,
     *     paginator: PaginatorDefaults,
     *     paginators: ServiceManagerConfiguration,
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'paginator'    => [
                'itemCountPerPage' => 10,
                'pageRange'        => 10,
                'scrollingStyle'   => 'Sliding',
            ],
            'paginators'   => [],
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
                Defaults::class             => DefaultsFactory::class,
                PaginatorFactory::class     => PaginatorFactoryFactory::class,
            ],
        ];
    }
}
