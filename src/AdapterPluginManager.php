<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for paginator adapters.
 *
 * Enforces that adapters retrieved are instances of
 * Adapter\AdapterInterface. Additionally, it registers a number of default
 * adapters available.
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
    protected $aliases = array(
        'null'                        => 'nullfill',
        'Laminas\Paginator\Adapter\Null' => 'nullfill',

        // Legacy Zend Framework aliases
        'Zend\Paginator\Adapter\Null' => 'nullfill',
    );

    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'array'         => 'Laminas\Paginator\Adapter\ArrayAdapter',
        'iterator'      => 'Laminas\Paginator\Adapter\Iterator',
        'nullfill'      => 'Laminas\Paginator\Adapter\NullFill',
    );

    /**
     * Default set of adapter factories
     *
     * @var array
     */
    protected $factories = array(
        'dbselect'         => 'Laminas\Paginator\Adapter\Service\DbSelectFactory',
        'dbtablegateway'   => 'Laminas\Paginator\Adapter\Service\DbTableGatewayFactory',
        'callback'         => 'Laminas\Paginator\Adapter\Service\CallbackFactory',
    );

    /**
     * Attempt to create an instance via a factory
     *
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return mixed
     * @throws \Laminas\ServiceManager\Exception\ServiceNotCreatedException If factory is not callable
     */
    protected function createFromFactory($canonicalName, $requestedName)
    {
        $factory = $this->factories[$canonicalName];
        if (is_string($factory) && class_exists($factory, true)) {
            $factory = new $factory($this->creationOptions);
            $this->factories[$canonicalName] = $factory;
        }
        return parent::createFromFactory($canonicalName, $requestedName);
    }

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance
     * of Adapter\AdapterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Adapter\AdapterInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Adapter\AdapterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
