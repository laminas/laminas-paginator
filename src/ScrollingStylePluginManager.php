<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function array_replace_recursive;

/**
 * Plugin manager implementation for scrolling style adapters
 *
 * Enforces that adapters retrieved are instances of
 * ScrollingStyleInterface. Additionally, it registers a number
 * of default adapters available.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @extends AbstractSingleInstancePluginManager<ScrollingStyleInterface>
 */
final class ScrollingStylePluginManager extends AbstractSingleInstancePluginManager
{
    private const DEFAULT_CONFIG = [
        'aliases'   => [
            'all'     => ScrollingStyle\All::class,
            'All'     => ScrollingStyle\All::class,
            'elastic' => ScrollingStyle\Elastic::class,
            'Elastic' => ScrollingStyle\Elastic::class,
            'jumping' => ScrollingStyle\Jumping::class,
            'Jumping' => ScrollingStyle\Jumping::class,
            'sliding' => ScrollingStyle\Sliding::class,
            'Sliding' => ScrollingStyle\Sliding::class,
        ],
        'factories' => [
            ScrollingStyle\All::class     => InvokableFactory::class,
            ScrollingStyle\Elastic::class => InvokableFactory::class,
            ScrollingStyle\Jumping::class => InvokableFactory::class,
            ScrollingStyle\Sliding::class => InvokableFactory::class,
        ],
    ];

    /** @var class-string */
    protected string $instanceOf = ScrollingStyleInterface::class;

    public function __construct(
        ContainerInterface $creationContext,
        array $config = [],
    ) {
        /** @psalm-var ServiceManagerConfiguration $config Psalm cannot infer this after merge */
        $config = array_replace_recursive(self::DEFAULT_CONFIG, $config);

        parent::__construct($creationContext, $config);
    }
}
