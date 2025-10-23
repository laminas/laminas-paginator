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

Finally, you'll need to assign the paginator instance to your view. If you're
using laminas-mvc and laminas-view, you can assign the paginator object to your view
model:

```php
$vm = new ViewModel();
$vm->setVariable('paginator', $paginator);
return $vm;
```

## Rendering pages with view scripts

> ### Installation Requirements
>
> The rendering with view scripts depends on the laminas-view component, so be sure
> to have it installed before getting started:
>
> ```bash
> $ composer require laminas/laminas-view
> ```

The view script is used to render the page items (if you're using
laminas-paginator to do so) and display the pagination control.

Because `Laminas\Paginator\Paginator` implements the SPL interface
[IteratorAggregate](http://php.net/IteratorAggregate), you can loop over an
instance using `foreach`:

```php
<html>
<body>
<h1>Example</h1>
<?php if (count($this->paginator)): ?>
<ul>
<?php foreach ($this->paginator as $item): ?>
  <li><?= $item; ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?= $this->paginationControl(
    $this->paginator,
    'Sliding',
    'my_pagination_control',
    ['route' => 'application/paginator']
) ?>
</body>
</html>
```

Notice the view helper call near the end. `PaginationControl` accepts up to four
parameters: the paginator instance, a scrolling style, a view script name, and
an array of additional parameters.

The second and third parameters are very important. Whereas the view script name
is used to determine how the pagination control should **look**, the scrolling
style is used to control how it should **behave**. Say the view script is in the
style of a search pagination control, like the one below:

![Pagination controls](images/usage-rendering-control.png)

What happens when the user clicks the "next" link a few times? Well, any number of things could
happen:

- The current page number could stay in the middle as you click through (as it
  does on Yahoo!)
- It could advance to the end of the page range and then appear again on the
  left when the user clicks "next" one more time.
- The page numbers might even expand and contract as the user advances (or
  "scrolls") through them (as they do on Google).

There are four scrolling styles packaged with Laminas:

Scrolling style | Description
--------------- | -----------
All             | Returns every page. This is useful for dropdown menu pagination controls with relatively few pages. In these cases, you want all pages available to the user at once.
Elastic         | A Google-like scrolling style that expands and contracts as a user scrolls through the pages.
Jumping         | As users scroll through, the page number advances to the end of a given range, then starts again at the beginning of the new range.
Sliding         | A Yahoo!-like scrolling style that positions the current page number in the center of the page range, or as close as possible. This is the default style.

The fourth and final parameter is reserved for an optional associative array of
variables that you want available in your view (available via `$this`). For
instance, these values could include extra URL parameters for pagination links.

By setting the default view script name, default scrolling style, and view
instance, you can eliminate the calls to `PaginationControl` completely:

```php
use Laminas\Paginator\Paginator;
use Laminas\View\Helper\PaginationControl;

Paginator::setDefaultScrollingStyle('Sliding');
PaginationControl::setDefaultViewPartial('my_pagination_control');
```

When all of these values are set, you can render the pagination control inside
your view script by echoing the paginator instance:

```php
<?= $this->paginator ?>
```

> ### Using other template engines
>
> Of course, it's possible to use laminas-paginator with other template engines.
> For example, with Smarty you might do the following:
>
> ```php
> $smarty-assign('pages', $paginator->getPages());
> ```
>
> You could then access paginator values from a template like so:
>
> ```php
> {$pages.pageCount}
> ```

### Example pagination controls

The following example pagination controls will help you get started with
laminas-view:

Search pagination:

```php
<!--
See http://developer.yahoo.com/ypatterns/pattern.php?pattern=searchpagination
-->

<?php if ($this->pageCount): ?>
<div class="paginationControl">
<!-- Previous page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->previous]); ?>">
    &lt; Previous
  </a> |
<?php else: ?>
  <span class="disabled">&lt; Previous</span> |
<?php endif; ?>

<!-- Numbered page links -->
<?php foreach ($this->pagesInRange as $page): ?>
  <?php if ($page != $this->current): ?>
    <a href="<?= $this->url($this->route, ['page' => $page]); ?>">
        <?= $page; ?>
    </a> |
  <?php else: ?>
    <?= $page; ?> |
  <?php endif; ?>
<?php endforeach; ?>

<!-- Next page link -->
<?php if (isset($this->next)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->next]); ?>">
    Next &gt;
  </a>
<?php else: ?>
  <span class="disabled">Next &gt;</span>
<?php endif; ?>
</div>
<?php endif; ?>
```

Item pagination:

```php
<!--
See http://developer.yahoo.com/ypatterns/pattern.php?pattern=itempagination
-->

<?php if ($this->pageCount): ?>
<div class="paginationControl">
<?= $this->firstItemNumber; ?> - <?= $this->lastItemNumber; ?>
of <?= $this->totalItemCount; ?>

<!-- First page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->first]); ?>">
    First
  </a> |
<?php else: ?>
  <span class="disabled">First</span> |
<?php endif; ?>

<!-- Previous page link -->
<?php if (isset($this->previous)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->previous]); ?>">
    &lt; Previous
  </a> |
<?php else: ?>
  <span class="disabled">&lt; Previous</span> |
<?php endif; ?>

<!-- Next page link -->
<?php if (isset($this->next)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->next]); ?>">
    Next &gt;
  </a> |
<?php else: ?>
  <span class="disabled">Next &gt;</span> |
<?php endif; ?>

<!-- Last page link -->
<?php if (isset($this->next)): ?>
  <a href="<?= $this->url($this->route, ['page' => $this->last]); ?>">
    Last
  </a>
<?php else: ?>
  <span class="disabled">Last</span>
<?php endif; ?>

</div>
<?php endif; ?>
```

Dropdown pagination:

```php
<?php if ($this->pageCount): ?>
<select id="paginationControl" size="1">
<?php foreach ($this->pagesInRange as $page): ?>
  <?php $selected = ($page == $this->current) ? ' selected="selected"' : ''; ?>
  <option value="<?= $this->url($this->route, ['page' => $page]);?>"<?= $selected ?>>
    <?= $page; ?>
  </option>
<?php endforeach; ?>
</select>
<?php endif; ?>

<script type="text/javascript"
     src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.2/prototype.js">
</script>
<script type="text/javascript">
$('paginationControl').observe('change', function() {
    window.location = this.options[this.selectedIndex].value;
})
</script>
```

### Listing of properties

The following options are available to pagination control view scripts:

Property         | Type    | Description
---------------- | ------- | ------------
first            | integer | First page number (typically 1).
firstItemNumber  | integer | Absolute number of the first item on this page.
firstPageInRange | integer | First page in the range returned by the scrolling style.
current          | integer | Current page number.
currentItemCount | integer | Number of items on this page.
itemCountPerPage | integer | Maximum number of items available to each page.
last             | integer | Last page number.
lastItemNumber   | integer | Absolute number of the last item on this page.
lastPageInRange  | integer | Last page in the range returned by the scrolling style.
next             | integer | Next page number.
pageCount        | integer | Number of pages.
pagesInRange     | array   | Array of pages returned by the scrolling style.
previous         | integer | Previous page number.
totalItemCount   | integer | Total number of items.
