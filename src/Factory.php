<?php // phpcs:disable Generic.NamingConventions.ConstructorName.OldStyle, WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function is_array;

abstract class Factory
{
    /**
     * Adapter plugin manager
     *
     * @var AdapterPluginManager|null
     */
    protected static $adapters;

    /**
     * Create adapter from items if necessary, and return paginator
     *
     * @param iterable $items
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
        $items   = $items['items'];

        return static::getAdapterFromManager($items, $adapter);
    }

    /**
     * Get adapter from manager if necessary, and return paginator
     *
     * @return Paginator
     */
    protected static function getAdapterFromManager(mixed $items, mixed $adapter)
    {
        if ($adapter instanceof AdapterInterface || $adapter instanceof AdapterAggregateInterface) {
            return new Paginator($adapter);
        }
        $adapter = static::getAdapterPluginManager()->get($adapter, $items);
        return new Paginator($adapter);
    }

    /**
     * Create paginator with items and adapter
     *
     * @return Paginator
     */
    public static function factory(mixed $items, mixed $adapter = null)
    {
        if (null === $adapter) {
            return static::createAdapterFromItems($items);
        }
        return static::getAdapterFromManager($items, $adapter);
    }

    /**
     * Change the adapter plugin manager
     *
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
            static::$adapters = new AdapterPluginManager(new ServiceManager());
        }
        return static::$adapters;
    }
}
