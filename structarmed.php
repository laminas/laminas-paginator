<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Exception', 'src/Exception')
    ->layer('LimitIterator', 'src/SerializableLimitIterator.php')
    ->layer('AdapterException', 'src/Adapter/Exception')
    ->layerPattern(
        'Adapter',
        '/^Laminas\\\\Paginator\\\\Adapter\\\\.*$/',
        [
            '/^Laminas\\\\Paginator\\\\Adapter\\\\Exception\\\\.*$/',
            '/^Laminas\\\\Paginator\\\\Adapter\\\\Service\\\\.*$/',
        ]
    )
    ->layer('ScrollingStyle', 'src/ScrollingStyle')
    ->layer('Paginator', [
        'src/AdapterAggregateInterface.php',
        'src/Pages.php',
        'src/Paginator.php',
        'src/PaginatorIterator.php',
    ])
    ->layer('Defaults', 'src/Defaults.php')
    ->layer('AdapterService', 'src/Adapter/Service')
    ->layer('Integration', [
        'src/AdapterPluginManager.php',
        'src/AdapterPluginManagerFactory.php',
        'src/ConfigProvider.php',
        'src/DefaultsFactory.php',
        'src/PaginatorFactory.php',
        'src/PaginatorFactoryFactory.php',
    ])
    ->ruleset([
        'Exception'        => [],
        'LimitIterator'    => [],
        'AdapterException' => ['Exception'],
        'Adapter'          => ['+AdapterException', 'LimitIterator'],
        'ScrollingStyle'   => ['Exception', 'Paginator'],
        'Paginator'        => ['+Adapter', '+ScrollingStyle'],
        'Defaults'         => ['ScrollingStyle'],
        'AdapterService'   => ['+Adapter', 'Defaults'],
        'Integration'      => ['+AdapterService', '+Paginator', '+Defaults'],
    ]);
