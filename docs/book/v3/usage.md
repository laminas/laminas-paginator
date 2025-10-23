# Usage

## Paginating data collections

In order to paginate items into pages, `Laminas\Paginator` must have a generic way
of accessing that data. For that reason, all data access takes place through
data source adapters. Several adapters ship with laminas-paginator by default:

Adapter        | Description
-------------- | -----------
ArrayAdapter   | Accepts a PHP array.
Iterator       | Accepts any `Iterator` instance.
NullFill       | Dummy paginator.

To create a paginator instance, you must supply an adapter to the constructor:

```php
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Paginator;

$paginator = new Paginator(new Adapter\ArrayAdapter($array));
```

In the case of the `NullFill` adapter, in lieu of a data collection you must
supply an item count to its constructor.

Although the instance is technically usable in this state, in your controller
action you'll need to tell the paginator what page number the user requested.
This allows advancing through the paginated data.

```php
$paginator->setCurrentPageNumber($page);
```

The simplest way to keep track of this value is through a URL parameter. The
following is an example [laminas-router](https://docs.laminas.dev/laminas-router/)
route configuration:

```php
return [
    'routes' => [
        'paginator' => [
            'type' => 'segment',
            'options' => [
                'route' => '/list/[page/:page]',
                'defaults' => [
                    'page' => 1,
                ],
            ],
        ],
    ],
];
```

With the above route (and using [laminas-mvc](https://docs.laminas.dev/laminas-mvc/)
controllers), you might set the current page number in your controller action
like so:

```php
$paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
```

There are other options available; see the [Configuration chapter](configuration.md)
for more on them.
