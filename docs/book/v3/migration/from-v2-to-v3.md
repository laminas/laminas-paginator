# Migrating from Version 2 to Version 3

Version 3 of Laminas Paginator contains several backward incompatible changes.

## New Features

### Laminas Service Manager v4 Support

`laminas-paginator` new requires `laminas-servicemanager` version 4, therefore allowing `psr/container` 2.0 installation.

### Re-worked, Optional Caching with `psr/cache`

With the [removal of caching](#caching) from the paginator itself, it is now possible to decorate adapters as needed with the `CachingAdapter`:

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\CachingAdapter;
use Laminas\Paginator\Paginator;

$paginator = new Paginator(
    new CachingAdapter(
        new ArrayAdapter($someData),
        'my-unique-prefix',
        $someCacheItemPoolInterface,
    ),
);
```

[Further Documentation](../caching.md)

## Bug Fixes & General Improvements

- Native parameter, property and return types have been added throughout the code base
- It is now possible to paginate associative arrays, or any type of iterable with string keys. Type inference for keys and values is retained.
- Type inference has been drastically improved for a more convenient developer experience. We strongly suggest usage of Psalm or PHPStan to benefit from these improvements.

## Feature Removal

### Caching

The caching features of the main Paginator class have been removed.
This means the following constants and methods of `Laminas\Paginator\Paginator` no longer exist:

- `CACHE_TAG_PREFIX` constant
- `setCache()`
- `setCacheEnabled()`
- `clearPageItemCache()`
- `getPageItemCache()`
- `cacheEnabled()`

### `laminas-view` integration

`Laminas\Paginator\Paginator` was unnecessarily, tightly coupled to Laminas View.
Because paginator is conceptually independent of a specific templating implementation, expect stand-alone integrations in the future.
The removal of Laminas view integration resulted in the following changes to `Laminas\Paginator\Paginator`:

- `Laminas\Paginator\Paginator` no longer implements the `Stringable` interface
- The following methods have been removed:
    - `__toString()`
    - `getView()`
    - `setView()`
    - `render()`

### Removal of Un-documented Filtering Feature

Previously, paginator allowed users to provide a `Laminas\Filter\FilterInterface` that would be used to filter/mutate the result set returned by adapters.
This feature has been removed.
It is unlikely the feature was widely used, and it had questionable merit because it would break the guarantees that a page of 10 items, would indeed contain 10 items.

The following methods have been removed from `Laminas\Paginator\Paginator`:

- `getFilter()`
- `setFilter()`

### Removal of the `ScrollingStylePluginManager`

Generally speaking, scrolling styles are relatively simple computations.
They can easily be "newed" when required.
You can still refer to them by 'name', for example _'Sliding'_.

Along with the plugin manger, a number of static methods associated with scrolling styles were removed from `Laminas\Paginator\Paginator`:

- `getDefaultScrollingStyle()`
- `setDefaultScrollingStyle()`
- `getDefaultItemCountPerPage()`
- `setDefaultItemCountPerPage()`
- `setScrollingStylePluginManager()`
- `getScrollingStylePluginManager()`

### Json encoding of Paginator Result Sets

The method `Laminas\Paginator\Paginator::toJson()` has been removed.
It is not a paginators concern to encode the paged items to any format.
If you relied on this functionality, it can be reproduced by encoding the current items yourself with `json_encode`, for example:

```php
$json = json_encode($paginator->getCurrentItems());
```

### Removal of "Global Config" via Static Properties

The method `Laminas\Paginator\Paginator::setGlobalConfig()` has been removed.
To create pagers with consistent and centralised configuration defaults, use the [newly introduced `PaginatorFactory`](../configuration.md#the-paginator-factory-service).

### Removal of Unused Exception Types

The following exceptions no longer exist:

- `Laminas\Paginator\Adapter\Exception\RuntimeException`
- `Laminas\Paginator\Adapter\Exception\UnexpectedValueException`
- `Laminas\Paginator\Exception\UnexpectedValueException`

### Laminas MVC Module Manager Support

The `Module` class has been removed.
It is possible to use the shipped `ConfigProvider` to ensure that the library is correctly configured for use with the application Service Manager.

### Removal of the `Factory` class

The class `Laminas\Paginator\Factory` has been removed.
Its conceptual functionality is replaced with the `Laminas\Paginator\PaginatorFactory` class which is [documented in detail here](../configuration.md#the-paginator-factory-service).
