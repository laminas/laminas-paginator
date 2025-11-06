# Configuration

`Laminas\Paginator` is designed to work with `laminas-servicemanager` as its dependency injection container.

It is perfectly possible to use Paginator without using Laminas Service Manager as the DIC in your project, but some convenience will be sacrificed.

## Configuring Pagination Defaults

The following configuration would be returned as part of the `config` service returned from the DI container, so it would be inserted into a PHP file that is autoloaded during bootstrap, or in a `ConfigProvider` perhaps:

```php
return [
    'paginator' => [
        'itemCountPerPage' => 10,
        'pageRange'        => 10,
        'scrollingStyle'   => 'Sliding',
    ],
];
```

The above configures the default page size to 10, the default page range to 10 and uses the `Sliding` scrolling style by default.

## The Paginator Factory Service

In order for these defaults to apply to newly created paginators, you must use the `PaginatorFactory` service to delegate construction of your paginators:

### Create a Paginator by Providing a Concrete Adapter

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\PaginatorFactory;

$factory = $container->get(PaginatorFactory::class);
$paginator = $factory->withAdapter(new ArrayAdapter([1, 2, 3]));
// The paginator above will now be configured with the defaults you have specified.
```

By using the `PaginatorFactory`, you can ensure consistent configuration of paginators across your application with a single place where these defaults can be changed, instead of hard-coding preferences into every call to `new Paginator(...)`.

The factory has 2 more methods for building paginators:

### Create a Paginator by Providing Configuration for an Adapter

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\PaginatorFactory;

$factory = $container->get(PaginatorFactory::class);
$paginator = $factory->buildAdapter(ArrayAdapter::class, ['some', 'items', 'to', 'paginate']);
```

### Create a Paginator with an Iterable

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\PaginatorFactory;

$factory = $container->get(PaginatorFactory::class);
$paginator = $factory->withItems(['some', 'items', 'to', 'paginate']);
```
