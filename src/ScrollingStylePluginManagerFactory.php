<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function is_array;

/**
 * @internal
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class ScrollingStylePluginManagerFactory implements FactoryInterface
{
    /** @inheritDoc */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): ScrollingStylePluginManager {
        $options = ! is_array($options) ? [] : $options;
        return new ScrollingStylePluginManager($container, $options);
    }
}
