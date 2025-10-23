<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function get_debug_type;
use function sprintf;

/**
 * Plugin manager implementation for paginator adapters.
 *
 * Enforces that adapters retrieved are instances of
 * AdapterInterface. Additionally, it registers a number of default
 * adapters available.
 *
 * @extends AbstractPluginManager<AdapterInterface|AdapterAggregateInterface>
 * @final
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default aliases
     *
     * Primarily for ensuring previously defined adapters select their
     * current counterparts.
     *
     * @var array
     */
    protected $aliases = [
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
    ];

    /**
     * Default set of adapter factories
     *
     * @var array
     */
    protected $factories = [
        Adapter\Callback::class     => Adapter\Service\CallbackFactory::class,
        Adapter\NullFill::class     => InvokableFactory::class,
        Adapter\Iterator::class     => Adapter\Service\IteratorFactory::class,
        Adapter\ArrayAdapter::class => InvokableFactory::class,
    ];

    /** @var string */
    protected $instanceOf = AdapterInterface::class;

    /**
     * Validate that a plugin is an adapter (v3)
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     * @psalm-assert AdapterInterface $instance
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                get_debug_type($instance),
                AdapterInterface::class
            ));
        }
    }

    /**
     * Validate that a plugin is an adapter (v2)
     *
     * @throws Exception\RuntimeException
     * @return void
     * @psalm-assert AdapterInterface $instance
     */
    public function validatePlugin(mixed $plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
