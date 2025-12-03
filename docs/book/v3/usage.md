# Usage

## Paginating data collections

In order to paginate items into pages, `Laminas\Paginator` must have a generic way of accessing that data.
For that reason, all data access takes place through data source adapters.
Several adapters ship with laminas-paginator by default:

| Adapter      | Description                                                            |
|--------------|------------------------------------------------------------------------|
| ArrayAdapter | Accepts a PHP array.                                                   |
| Callback     | Accepts closures that return underlying data sets.                     |
| Iterator     | Accepts any `Iterator` instance.                                       |
| NullFill     | Produces a configurable volume of nulls, typically useful for testing. |

To create a paginator instance, you must supply an adapter to the constructor:

```php
use Laminas\Paginator\Adapter;
use Laminas\Paginator\Paginator;

$paginator = new Paginator(new Adapter\ArrayAdapter($array));
```

Although the instance is technically usable in this state, in your application, you'll need to tell the paginator what page number the user requested.
This allows advancing through the paginated data.

```php
$paginator->setCurrentPageNumber($page);
```

The simplest way to keep track of this value is through a URL parameter.
The following is an example using [laminas-router](https://docs.laminas.dev/laminas-router/) route configuration:

```php
return [
    'routes' => [
        'some-route' => [
            'type' => 'segment',
            'options' => [
                'route' => '/show-something/[page/:page]',
                'defaults' => [
                    'page' => 1,
                ],
            ],
        ],
    ],
];
```

With the above route (and using [laminas-mvc](https://docs.laminas.dev/laminas-mvc/) controllers), you might set the current page number in your controller action like so:

```php
$paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
```

Now we have a paginator filled with data, and the current page number is known, we can iterate over the paginated items:

```php
foreach ($paginator as $item) {
    // Display the _item_ somehow
}
```

## Setting Page Size and Page Range

During construction, `Paginator` can accept values for `$itemCountPerPage` _(page size)_ and `$pageRange` _(The number of pages to "display")_:

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;

$paginator = new Paginator(new ArrayAdapter($array), 12, 5);
```

Page range and page size can be changed at runtime via instance setters:

```php
/** @var \Laminas\Paginator\Paginator $paginator */
$paginator->setItemCountPerPage(6);
$paginator->setPageRange(4);
```

### Default Settings

It is also possible to set _defaults_ for paginators, providing you make use of the `PaginatorFactory` service.
This is useful when you have project-wide defaults for consistent presentation.

More information about setting and overriding defaults can be found in the [configuration chapter](configuration.md).

Without specifying constructor arguments for page size and range, a value of `10` will be used for both.

## Pagination Controls

In order to be more flexible for users of other templating systems, `laminas-paginator` no longer comes with built-in templates and rendering of pagination controls.
In future, we may release dedicated libraries for popular templating engines.
In the short-term, this library exposes a value object representing the current state of the paginator for the purposes of rendering pagination controls:

```php
namespace Laminas\Paginator;

final readonly class Pages
{
    /**
     * @param int<0, max> $pageCount
     * @param int<1, max> $itemCountPerPage
     * @param int<1, max> $first
     * @param int<1, max> $current
     * @param int<1, max> $last
     * @param int<1, max>|null $previous
     * @param int<2, max>|null $next
     * @param array<int, int> $pagesInRange
     * @param int<1, max> $firstPageInRange
     * @param int<1, max> $lastPageInRange
     * @param int<0, max> $currentItemCount
     * @param int<0, max> $totalItemCount
     * @param int<0, max> $firstItemNumber
     * @param int<0, max> $lastItemNumber
     */
    public function __construct(
        public int $pageCount,
        public int $itemCountPerPage,
        public int $first,
        public int $current,
        public int $last,
        public int|null $previous,
        public int|null $next,
        public array $pagesInRange,
        public int $firstPageInRange,
        public int $lastPageInRange,
        public int $currentItemCount,
        public int $totalItemCount,
        public int $firstItemNumber,
        public int $lastItemNumber,
    ) {
    }
}
```

### Scrolling Styles

`laminas-paginator` ships 4 "scrolling styles" that emulate common pagination views.
Scrolling styles are responsible for computing which pages should be visible when there are many.

#### All

The `All` style is the easiest to consider as it simply ignores the page range returning every page:

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\All;

// 20 Pages of data
$paginator = new Paginator(
    adapter: new ArrayAdapter(range(1, 200)),
    itemCountPerPage: 10,
    pageRange: 5,
);

$pages = $paginator->getPages(new All());

var_dump($pages->pagesInRange); // [1 => 1, 2 => 2, ..., 200 => 200]
```

#### Elastic

`Elastic` is much like Google's pagination - the deeper in the set the current page number is, the more pages are shown:

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\Elastic;

// 20 Pages of data
$paginator = new Paginator(
    adapter: new ArrayAdapter(range(1, 200)),
    itemCountPerPage: 10,
    pageRange: 5,
);

$pages = $paginator->getPages(new Elastic());

var_dump($pages->pagesInRange); // Pages 1 - 5

$paginator->setCurrentPageNumber(10);

$pages = $paginator->getPages(new Elastic());

var_dump($pages->pagesInRange); // Pages 6 - 14. Note that 8 pages are shown here instead of 5
```

#### Sliding

The `Sliding` style keeps the current page number in the middle, keeping the page-range consistent.

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\Sliding;

// 20 Pages of data
$paginator = new Paginator(
    adapter: new ArrayAdapter(range(1, 200)),
    itemCountPerPage: 10,
    pageRange: 5,
);

$pages = $paginator->getPages(new Sliding());

var_dump($pages->pagesInRange); // Pages 1 - 5

$paginator->setCurrentPageNumber(10);

$pages = $paginator->getPages(new Sliding());

var_dump($pages->pagesInRange); // Pages 8 - 12
```

#### Jumping

The `Jumping` style yields the same chunk of pages for as long as the page number is within range:

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\ScrollingStyle\Jumping;

// 20 Pages of data
$paginator = new Paginator(
    adapter: new ArrayAdapter(range(1, 200)),
    itemCountPerPage: 10,
    pageRange: 5,
);

$paginator->setCurrentPageNumber(5);
$pages = $paginator->getPages(new Jumping());

var_dump($pages->pagesInRange); // Pages 1 - 5

$paginator->setCurrentPageNumber(6);
$pages = $paginator->getPages(new Jumping());

var_dump($pages->pagesInRange); // Pages 6 - 10
```

When providing the chosen scrolling style to either the paginator constructor, or to its `getPages()` method, you can choose to provide an instance, or a string.
When providing a string, note that it is case-insensitive, so `sliding`, `Sliding` and `SlIDiNg` are all equivalent.

| Scrolling Style                            | Acceptable string |
|--------------------------------------------|-------------------|
| `Laminas\Paginator\ScrollingStyle\All`     | all               |
| `Laminas\Paginator\ScrollingStyle\Elastic` | elastic           |
| `Laminas\Paginator\ScrollingStyle\Sliding` | sliding           |
| `Laminas\Paginator\ScrollingStyle\Jumping` | jumping           |

### Custom Scrolling Styles

You can use custom scrolling styles by implementing `Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface`.
**You cannot refer to custom implementations with a string**, so you must provide an instance to the paginator constructor, or to its `getPages()` method.

```php
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use My\Custom\ScrollingStyle;

$paginator = new Paginator(
    adapter: new ArrayAdapter(range(1, 200)),
    itemCountPerPage: 10,
    pageRange: 5,
    scrollingStyle: new ScrollingStyle(),
);

// or provide the style when retrieving the Pages value object:

$paginator->getPages(new ScrollingStyle());
```

For further details on the other available options; see the [Configuration chapter](configuration.md).
