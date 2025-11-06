<?php

declare(strict_types=1);

namespace Laminas\Paginator;

/**
 * @deprecated Since 2.22.0. This class will be removed in 3.0.0 without replacement.
 *
 * @final
 */
class Module
{
    /**
     * Retrieve default laminas-paginator config for laminas-mvc context.
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
