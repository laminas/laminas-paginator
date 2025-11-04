<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class Module
{
    /**
     * Retrieve default laminas-paginator config for laminas-mvc context.
     *
     * @return array{service_manager: ServiceManagerConfiguration}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
