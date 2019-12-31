<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for scrolling style adapters
 *
 * Enforces that adapters retrieved are instances of
 * ScrollingStyle\ScrollingStyleInterface. Additionally, it registers a number
 * of default adapters available.
 *
 * @category   Laminas
 * @package    Laminas_Paginator
 */
class ScrollingStylePluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'all'     => 'Laminas\Paginator\ScrollingStyle\All',
        'elastic' => 'Laminas\Paginator\ScrollingStyle\Elastic',
        'jumping' => 'Laminas\Paginator\ScrollingStyle\Jumping',
        'sliding' => 'Laminas\Paginator\ScrollingStyle\Sliding',
    );

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance of ScrollingStyle\ScrollingStyleInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ScrollingStyle\ScrollingStyleInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\ScrollingStyle\ScrollingStyleInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}

