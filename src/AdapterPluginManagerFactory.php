<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayAccess;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

/**
 * @internal
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class AdapterPluginManagerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array|null $options = null,
    ): AdapterPluginManager {
        $config = $container->has('config') ? $container->get('config') : [];
        assert(is_array($config) || $config instanceof ArrayAccess);
        /** @psalm-var ServiceManagerConfiguration $options */
        $options = $config['paginators'] ?? [];

        return new AdapterPluginManager($container, $options);
    }
}
