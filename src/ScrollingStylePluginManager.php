<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function gettype;
use function is_object;
use function sprintf;

/**
 * Plugin manager implementation for scrolling style adapters
 *
 * Enforces that adapters retrieved are instances of
 * ScrollingStyleInterface. Additionally, it registers a number
 * of default adapters available.
 *
 * @extends AbstractPluginManager<ScrollingStyleInterface>
 * @psalm-import-type FactoriesConfigurationType from ConfigInterface
 */
class ScrollingStylePluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array<array-key, string>
     */
    protected $aliases = [
        'all'     => ScrollingStyle\All::class,
        'All'     => ScrollingStyle\All::class,
        'elastic' => ScrollingStyle\Elastic::class,
        'Elastic' => ScrollingStyle\Elastic::class,
        'jumping' => ScrollingStyle\Jumping::class,
        'Jumping' => ScrollingStyle\Jumping::class,
        'sliding' => ScrollingStyle\Sliding::class,
        'Sliding' => ScrollingStyle\Sliding::class,

        // Legacy Zend Framework aliases
        'Zend\Paginator\ScrollingStyle\All'     => ScrollingStyle\All::class,
        'Zend\Paginator\ScrollingStyle\Elastic' => ScrollingStyle\Elastic::class,
        'Zend\Paginator\ScrollingStyle\Jumping' => ScrollingStyle\Jumping::class,
        'Zend\Paginator\ScrollingStyle\Sliding' => ScrollingStyle\Sliding::class,

        // v2 normalized FQCNs
        'zendpaginatorscrollingstyleall'     => ScrollingStyle\All::class,
        'zendpaginatorscrollingstyleelastic' => ScrollingStyle\Elastic::class,
        'zendpaginatorscrollingstylejumping' => ScrollingStyle\Jumping::class,
        'zendpaginatorscrollingstylesliding' => ScrollingStyle\Sliding::class,
    ];

    /**
     * Default set of adapter factories
     *
     * @var FactoriesConfigurationType
     */
    protected $factories = [
        ScrollingStyle\All::class     => InvokableFactory::class,
        ScrollingStyle\Elastic::class => InvokableFactory::class,
        ScrollingStyle\Jumping::class => InvokableFactory::class,
        ScrollingStyle\Sliding::class => InvokableFactory::class,

        // v2 normalized names
        'laminaspaginatorscrollingstyleall'     => InvokableFactory::class,
        'laminaspaginatorscrollingstyleelastic' => InvokableFactory::class,
        'laminaspaginatorscrollingstylejumping' => InvokableFactory::class,
        'laminaspaginatorscrollingstylesliding' => InvokableFactory::class,
    ];

    /** @inheritDoc */
    protected $instanceOf = ScrollingStyleInterface::class;

    /**
     * Validate a plugin (v3)
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     * @psalm-assert ScrollingStyleInterface $instance
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                is_object($instance) ? $instance::class : gettype($instance),
                ScrollingStyleInterface::class
            ));
        }
    }

    /**
     * Validate a plugin (v2)
     *
     * @throws Exception\InvalidArgumentException
     * @return void
     * @psalm-assert ScrollingStyleInterface $instance
     */
    public function validatePlugin(mixed $plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
