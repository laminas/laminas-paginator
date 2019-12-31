<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

abstract class Factory
{
    /**
     * Adapter plugin manager
     * @var AdapterPluginManager
     */
    protected static $adapters;

    /**
     * Create adapter from items if necessary, and return paginator
     * @param Traversable/array $items
     * @return Paginator
     */
    protected static function createAdapterFromItems($items)
    {
        if ($items instanceof Traversable) {
            $items = ArrayUtils::iteratorToArray($items);
        }
        if (! is_array($items)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument when '
                . "it's used with one parameter"
            );
        }
        if (! isset($items['adapter']) && ! isset($items['items'])) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object with keys '
                . '"adapter" and "items"'
            );
        }
        $adapter = $items['adapter'];
        $items = $items['items'];

        $paginator = static::getAdapterFromManager($items, $adapter);
        return $paginator;
    }

    /**
     * Get adapter from manager if necessary, and return paginator
     * @param mixed $items
     * @param mixed $adapter
     * @return Paginator
     */
    protected static function getAdapterFromManager($items, $adapter)
    {
        if ($adapter instanceof AdapterInterface || $adapter instanceof AdapterAggregateInterface) {
            return new Paginator($adapter);
        }
        $adapter = static::getAdapterPluginManager()->get($adapter, $items);
        return new Paginator($adapter);
    }

    /**
     * Create paginator with items and adapter
     * @param mixed $items
     * @param mixed $adapter
     * @return Paginator
     */
    public static function factory($items, $adapter = null)
    {
        if (null === $adapter) {
            $paginator = static::createAdapterFromItems($items);
            return $paginator;
        }
        $paginator = static::getAdapterFromManager($items, $adapter);
        return $paginator;
    }

    /**
     * Change the adapter plugin manager
     *
     * @param  AdapterPluginManager $adapters
     * @return void
     */
    public static function setAdapterPluginManager(AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new AdapterPluginManager(new ServiceManager);
        }
        return static::$adapters;
    }
}
