# Stand-Alone

The paginator can be used stand-alone, outside a Mezzio or laminas-mvc application.

The example uses the following directory structure:

```treeview
example-app/
|-- public/
|   `-- index.php
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
