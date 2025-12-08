# Advanced Usage

## Using the Paginator Adapter Plugin Manager

laminas-paginator ships with a plugin manager for adapters, `Laminas\Paginator\AdapterPluginManager`.
The plugin manager can be used to retrieve adapters.
Since most adapters require constructor arguments, they may be passed as the second argument to the `build()` method in the same order they appear in the constructor.

### Examples

```php
use Laminas\Paginator\Adapter;
use Laminas\Paginator\AdapterPluginManager;

$pluginManager = new AdapterPluginManager();

// Get an array adapter for an array of items
$arrayAdapter = $pluginManager->build(Adapter\ArrayAdapter::class, [$arrayOfItems]);

// Get an Iterator adapter based on an iterator:
$iteratorAdapter = $pluginManager->build(Adapter\Iterator::class, [$iterator]);
```

## Custom Data Source Adapters

At some point you may run across a data type that is not covered by the packaged adapters.
In this case, you will need to write your own.

To do so, you must implement `Laminas\Paginator\Adapter\AdapterInterface`.
There are two methods required to do this:

- `count(): int`
- `getItems(int $offset, int $itemCountPerPage): iterable`

Additionally, you'll typically implement a constructor that takes your data source as a parameter.

If you've ever used the SPL interface [Countable](http://php.net/Countable), you're familiar with `count()`.
As used with `laminas-paginator`, this is the total number of items in the data collection; `Laminas\Paginator\Paginator::countAllItems` proxies to this method.

When retrieving items for the current page, `Laminas\Paginator\Paginator` calls on your adapter's `getItems()` method, providing it with an offset and the number of items to display per page;
your job is to return the appropriate slice of data.
For an array, that would be:

```php
return array_slice($this->array, $offset, $itemCountPerPage);
```

Take a look at the packaged adapters for ideas of how you might go about implementing your own.

### Registering Your Adapter with the Plugin Manager

If you want to register your adapter with the `Laminas\Pagiantor\AdapterPluginManager`, you can do so via configuration.
The "paginators" configuration key can contain [standard laminas-servicemanager-style configuration](https://docs.laminas.dev/laminas-servicemanager/configuring-the-service-manager/).

One possibility is to add it to the `config/autoload/global.php` file:

```php
return [
    // ...
    'paginators' => [
        'factories' => [
            YourCustomPaginationAdapter::class => YourCustomPaginationAdapterFactory::class,
        ],
    ],
];
```

This allows you to retrieve the `AdapterPluginManager` in a factory, and then pull your adapter from it.
As an example, consider the following factory:

```php
use Laminas\Paginator\AdapterPluginManager;
use Laminas\Paginator\Paginator;
use Psr\Container\ContainerInterface;

class SomeServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $paginators = $container->get(AdapterPluginManager::class);
        $paginator  = new Paginator($paginators->build(YourCustomPaginatorAdapter::class, $someOptions));
        // ...
    }
}
```
