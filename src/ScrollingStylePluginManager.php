<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for scrolling style adapters
 *
 * Enforces that adapters retrieved are instances of
 * ScrollingStyle\ScrollingStyleInterface. Additionally, it registers a number
 * of default adapters available.
 */
class ScrollingStylePluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
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
        \Zend\Paginator\ScrollingStyle\All::class => ScrollingStyle\All::class,
        \Zend\Paginator\ScrollingStyle\Elastic::class => ScrollingStyle\Elastic::class,
        \Zend\Paginator\ScrollingStyle\Jumping::class => ScrollingStyle\Jumping::class,
        \Zend\Paginator\ScrollingStyle\Sliding::class => ScrollingStyle\Sliding::class,

        // v2 normalized FQCNs
        'zendpaginatorscrollingstyleall' => ScrollingStyle\All::class,
        'zendpaginatorscrollingstyleelastic' => ScrollingStyle\Elastic::class,
        'zendpaginatorscrollingstylejumping' => ScrollingStyle\Jumping::class,
        'zendpaginatorscrollingstylesliding' => ScrollingStyle\Sliding::class,
    ];

    /**
     * Default set of adapter factories
     *
     * @var array
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

    protected $instanceOf = ScrollingStyle\ScrollingStyleInterface::class;

    /**
     * Validate a plugin (v3)
     *
     * @param mixed $plugin
     * @throws InvalidServiceException
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                Adapter\AdapterInterface::class
            ));
        }
    }

    /**
     * Validate a plugin (v2)
     *
     * @param mixed $plugin
     * @throws Exception\InvalidArgumentException
     */
    public function validatePlugin($plugin)
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
