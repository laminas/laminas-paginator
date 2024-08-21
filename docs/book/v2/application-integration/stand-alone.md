# Stand-Alone

The paginator can also be used stand-alone, outside of a Mezzio or laminas-mvc
application.

The example uses the following directory structure:

```treeview
example-app/
|-- public/
|   `-- index.php
|-- templates/
|   `-- pagination-control.phtml
`-- vendor
    `-- …
```

## Create and Configure Paginator

[Create a paginator and a related adapter](../usage.md#paginating-data-collections),
set the item count for one page and the current page number in `public/index.php`:

```php
// Create paginator
$paginator = new Laminas\Paginator\Paginator(
    new Laminas\Paginator\Adapter\ArrayAdapter($albums)
);

// Configure paginator
$paginator->setItemCountPerPage(4);
$paginator->setCurrentPageNumber((int) ($_GET['page'] ?? 1));
```

<!-- markdownlint-disable-next-line MD033-->
<details><summary>Example Data</summary>

```php
$albums = [
    [
        'artist' => 'David Bowie',
        'title'  => 'The Next Day (Deluxe Version)',
    ],
    [
        'artist' => 'Bastille',
        'title'  => 'Bad Blood',
    ],
    [
        'artist' => 'Bruno Mars',
        'title'  => 'Unorthodox Jukebox',
    ],
    [
        'artist' => 'Emeli Sandé',
        'title'  => 'Our Version of Events (Special Edition)',
    ],
    [
        'artist' => 'Bon Jovi',
        'title'  => 'What About Now (Deluxe Version)',
    ],
    [
        'artist' => 'Justin Timberlake',
        'title'  => 'The 20/20 Experience (Deluxe Version)',
    ],
    [
        'artist' => 'Bastille',
        'title'  => 'Bad Blood (The Extended Cut)',
    ],
    [
        'artist' => 'P!nk',
        'title'  => 'The Truth About Love',
    ],
    [
        'artist' => 'Sound City - Real to Reel',
        'title'  => 'Sound City - Real to Reel',
    ],
    [
        'artist' => 'Jake Bugg',
        'title'  => 'Jake Bugg',
    ],
];
```

<!-- markdownlint-disable-next-line MD033-->
</details>

## Output Pure Data

The data of each sub-array is returned by iteration over the paginator:

```php
foreach ($paginator as $item) {
    var_dump($item['artist']); // "Bon Jovi", "Justin Timberlake", …
    var_dump($item['title']); // "What About Now (Deluxe Version)", "The 20/20 Experience (Deluxe Version)", …
}
```

Retrieving the [current status data of the paginator](../usage.md#listing-of-properties):

```php
var_dump($paginator->getPages()->previous); // 1
var_dump($paginator->getPages()->next); // 3
```

## Usage with laminas-view

### Create View Script

[Create a view script](https://docs.laminas.dev/laminas-view/view-scripts/) in
`templates/pagination-control.phtml`:

```php
<?php
/**
 * @var Laminas\View\Renderer\PhpRenderer $this
 * @var int                               $pageCount
 * @var int                               $previous
 * @var int                               $next
 * @var int                               $current
 * @var array<int, int>                   $pagesInRange
 */
?>

<?php if ($pageCount): ?>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
        <!-- Previous page link -->
        <?php if (isset($previous)): ?>
            <li class="page-item">
                <a class="page-link" href="index.php?page=<?= $previous ?>">Previous</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
            </li>
        <?php endif; ?>

        <!-- Numbered page links -->
        <?php foreach ($pagesInRange as $page): ?>
            <?php if ($page !== $current): ?>
                <li class="page-item">
                    <a class="page-link" href="index.php?page=<?= $page ?>">
                        <?= $page ?>
                    </a>
                </li>
            <?php else: ?>
                <!-- Current page -->
                <li class="page-item active" aria-current="page">
                    <a class="page-link" href="#"><?= $page ?> <span class="sr-only">(current)</span></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Next page link -->
        <?php if (isset($this->next)): ?>
            <li class="page-item">
                <a class="page-link" href="index.php?page=<?= $next ?>">Next</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
            </li>
        <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
```

### Setup

[Set a resolver for templates](https://docs.laminas.dev/laminas-view/php-renderer/#usage)
and set template for the [related view helper](../usage.md#rendering-pages-with-view-scripts)
in `public/index.php`:

```php
// Create template resolver
$templateResolver = new Laminas\View\Resolver\TemplatePathStack([
    'script_paths' => [__DIR__ . '/../templates'],
]);

// Setup renderer
/** @var Laminas\View\Renderer\PhpRenderer $renderer */
$renderer = $paginator->getView();
$renderer->setResolver($templateResolver);

// Set template for related view helper
$renderer->plugin('paginationControl')->setDefaultViewPartial('pagination-control');
```

### Render Output

```php
echo $paginator->render();
```

Output:

```html
<nav aria-label="Page navigation example">
    <ul class="pagination">
        <!-- Previous page link -->
        <li class="page-item">
            <a class="page-link" href="index.php?page=1">Previous</a>
        </li>

        <!-- Numbered page links -->
        <li class="page-item">
            <a class="page-link" href="index.php?page=1">
                1
            </a>
        </li>
        <!-- Current page -->
        <li class="page-item active" aria-current="page">
            <a class="page-link" href="#">2 <span class="sr-only">(current)</span></a>
        </li>
        <li class="page-item">
            <a class="page-link" href="index.php?page=3">
                3
            </a>
        </li>

        <!-- Next page link -->
        <li class="page-item">
            <a class="page-link" href="index.php?page=3">Next</a>
        </li>
    </ul>
</nav>
```

<!-- markdownlint-disable-next-line MD033-->
<details><summary>Show full code example</summary>

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$albums = [
    [
        'artist' => 'David Bowie',
        'title'  => 'The Next Day (Deluxe Version)',
    ],
    [
        'artist' => 'Bastille',
        'title'  => 'Bad Blood',
    ],
    [
        'artist' => 'Bruno Mars',
        'title'  => 'Unorthodox Jukebox',
    ],
    [
        'artist' => 'Emeli Sandé',
        'title'  => 'Our Version of Events (Special Edition)',
    ],
    [
        'artist' => 'Bon Jovi',
        'title'  => 'What About Now (Deluxe Version)',
    ],
    [
        'artist' => 'Justin Timberlake',
        'title'  => 'The 20/20 Experience (Deluxe Version)',
    ],
    [
        'artist' => 'Bastille',
        'title'  => 'Bad Blood (The Extended Cut)',
    ],
    [
        'artist' => 'P!nk',
        'title'  => 'The Truth About Love',
    ],
    [
        'artist' => 'Sound City - Real to Reel',
        'title'  => 'Sound City - Real to Reel',
    ],
    [
        'artist' => 'Jake Bugg',
        'title'  => 'Jake Bugg',
    ],
];

// Create paginator
$paginator = new Laminas\Paginator\Paginator(
    new Laminas\Paginator\Adapter\ArrayAdapter($albums)
);
$paginator->setItemCountPerPage(4);
$paginator->setCurrentPageNumber((int) ($_GET['page'] ?? 1));

// Create template resolver
$templateResolver = new Laminas\View\Resolver\TemplatePathStack([
    'script_paths' => [__DIR__ . '/../templates'],
]);

// Setup renderer
/** @var Laminas\View\Renderer\PhpRenderer $renderer */
$renderer = $paginator->getView();
$renderer->setResolver($templateResolver);

// Set template for related view helper
$renderer->plugin('paginationControl')->setDefaultViewPartial('pagination-control');

// Render output
echo $paginator->render();
```

<!-- markdownlint-disable-next-line MD033-->
</details>
