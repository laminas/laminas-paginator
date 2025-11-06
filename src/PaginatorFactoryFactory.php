<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class PaginatorFactoryFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): PaginatorFactory {
        return new PaginatorFactory(
            $container->get(AdapterPluginManager::class),
            $container->get(Defaults::class),
        );
    }
}
