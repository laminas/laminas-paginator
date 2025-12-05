# Caching paginator pages

It is possible to cache pagination results by wrapping adapters in the shipped `CachingAdapter`.
The `CachingAdapter` requires a pagination adapter, a PSR cache item pool and a unique cache-key prefix.

```php
use DateInterval;
use Laminas\Paginator\Adapter\AdapterInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CachingAdapter implements AdapterInterface
{
    /**
     * @param AdapterInterface<TKey, TValue> $adapter
     * @param non-empty-string $cacheKeyPrefix
     */
    public function __construct(
        private AdapterInterface $adapter,
        private string $cacheKeyPrefix,
        private CacheItemPoolInterface $cache,
        private DateInterval|null $ttl,
    ) {
    }

    // ...
}
```

To _manually_ create a paginator that will cache results as they are retrieved, the following would be required:

```php
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\CachingAdapter;
use Laminas\Paginator\Paginator;
use Psr\Cache\CacheItemPoolInterface;

$adapter = new \SpecialCustomAdapter(/** With some data that needs to be cached */);

// Assume we have some ready-prepared objects:
assert($cachePool instanceof CacheItemPoolInterface);
assert($adapter instanceof AdapterInterface);

$paginator = new Paginator(
    new CachingAdapter(
        $adapter,
        'my-unique-prefix',
        $cachePool,
        new DateInterval('PT30M'),
    ),
    10,
    10,
    'Sliding',
);
```

The above example assumes that `\SpecialCustomAdapter` is an adapter that makes expensive database calls or other I/O where caching is a useful performance improvement.

The cache pool argument can be any [PSR-6](https://www.php-fig.org/psr/psr-6/) implementation.
[laminas-cache](https://docs.laminas.dev/laminas-cache/v4/psr6/) provides a PSR-6 implementation along with [many other libraries](https://packagist.org/providers/psr/cache-implementation).

Paginator assumes that you will likely use a single cache pool for all paginators, therefore a unique key prefix is required to prevent cache-key collisions with other paginator instances used elsewhere in your application.

Finally, you can also provide an optional TTL in the form of a [`DateInterval`](https://www.php.net/manual/class.dateinterval.php) object.

## `PaginatorFactory` integration and setting defaults

The shipped `PaginatorFactory` class is capable of decorating adapters for you, providing some configuration is in-place, thereby reducing the previous example to:

```php
use Laminas\Paginator\PaginatorFactory;
use Psr\Container\ContainerInterface;

assert($container instanceof ContainerInterface);

$adapter = new \SpecialCustomAdapter(/** With some data that needs to be cached */);

$factory = $container->get(PaginatorFactory::class);
$paginator = $factory->withCachingAdapter($adapter, 'my-unique-prefix');
```

Using the factory assumes that a single cache item pool will be used for all paginators _(Which is why the unique prefix is necessary)_.
To take advantage of the paginator factory, you must ensure that you configure the necessary services.
The following example assumes that you are using [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager) for dependency injection.

```php
return [
    // Laminas Service Manager Configuration:
    'dependencies' => [
        'factories' => [
            'PaginatorCachePool' => \SomeFactoryThatWillProduceACacheItemPoolInterface::class,
        ],
    ],
    
    // Telling the paginator factory which "Service" to pull from the container:
    'paginator' => [
        'defaultCache' => 'PaginatorCachePool',
        'defaultCacheTTL' => 'PT30M',
    ],
];
```

Further information:

- [Custom Paginator Adapters](./advanced.md#custom-data-source-adapters)
- [More information on writing factories with Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/v4/configuring-the-service-manager/#factories)
- [Using Laminas Cache as a PSR-6 Cache Pool](https://docs.laminas.dev/laminas-cache/v4/psr6/)
- [The PSR-6 Specification](https://www.php-fig.org/psr/psr-6/)
