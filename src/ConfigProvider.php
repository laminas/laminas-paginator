<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use DateInterval;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-type PaginatorDefaults = array{
 *     itemCountPerPage?: int<1, max>,
 *     pageRange?: int<1, max>,
 *     scrollingStyle?: string|ScrollingStyleInterface,
 *     defaultCache?: string|null,
 *     defaultCacheTTL?: string|int|DateInterval|null,
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
                /**
                 * This value should be a string representing a cache pool that can be retrieved from the DI container
                 * if you want to be able to easily create caching paginators.
                 */
                'defaultCache' => null,
                /**
                 * This value should be a string that can be understood by DateInterval, for example, a 30 minute TTL
                 * would be "PT30M". Null is also acceptable and means that paginated items will not expire until
                 * manually flushed.
                 *
                 * @see https://www.php.net/manual/dateinterval.construct.php
                 */
                'defaultCacheTTL' => null,
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
