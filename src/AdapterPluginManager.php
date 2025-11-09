<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function array_replace_recursive;
use function get_debug_type;
use function sprintf;

/**
 * Plugin manager implementation for paginator adapters.
 *
 * Enforces that adapters retrieved are instances of AdapterInterface.
 * Additionally, it registers a number of default adapters.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @extends AbstractPluginManager<AdapterInterface|AdapterAggregateInterface>
 */
final class AdapterPluginManager extends AbstractPluginManager
{
    private const DEFAULT_CONFIG = [
        'aliases'   => [
            'callback' => Adapter\Callback::class,
            'Callback' => Adapter\Callback::class,
            'null'     => Adapter\NullFill::class,
            'Null'     => Adapter\NullFill::class,
            'nullfill' => Adapter\NullFill::class,
            'nullFill' => Adapter\NullFill::class,
            'NullFill' => Adapter\NullFill::class,
            'array'    => Adapter\ArrayAdapter::class,
            'Array'    => Adapter\ArrayAdapter::class,
            'iterator' => Adapter\Iterator::class,
            'Iterator' => Adapter\Iterator::class,
        ],
        'factories' => [
            Adapter\CachingAdapter::class => Adapter\Service\CachingAdapterFactory::class,
            Adapter\Callback::class       => Adapter\Service\CallbackFactory::class,
            Adapter\NullFill::class       => Adapter\Service\NullFillFactory::class,
            Adapter\Iterator::class       => Adapter\Service\IteratorFactory::class,
            Adapter\ArrayAdapter::class   => InvokableFactory::class,
        ],
    ];

    public function __construct(
        ContainerInterface $creationContext,
        array $config = [],
    ) {
        /** @psalm-var ServiceManagerConfiguration $config Psalm cannot infer this after merge */
        $config = array_replace_recursive(self::DEFAULT_CONFIG, $config);

        parent::__construct($creationContext, $config);
    }

    public function validate(mixed $instance): void
    {
        if (! $instance instanceof AdapterInterface && ! $instance instanceof AdapterAggregateInterface) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                get_debug_type($instance),
                AdapterInterface::class
            ));
        }
    }
}
