<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\CachingAdapter;
use Laminas\Paginator\Exception\RuntimeException;

use function assert;
use function is_array;
use function iterator_to_array;

final readonly class PaginatorFactory
{
    public function __construct(
        private AdapterPluginManager $pluginManager,
        private Defaults $defaults,
    ) {
    }

    /**
     * Generate a Paginator using the given adapter with configured defaults for page size etc.
     *
     * @template TKey of array-key
     * @template TValue
     * @param AdapterInterface<TKey, TValue> $adapter
     * @return Paginator<TKey, TValue>
     */
    public function withAdapter(AdapterInterface $adapter): Paginator
    {
        return new Paginator(
            $adapter,
            $this->defaults->itemCountPerPage,
            $this->defaults->pageRange,
            $this->defaults->scrollingStyle,
        );
    }

    /**
     * Generate a paginator with the given adapter wrapped in a caching adapter.
     *
     * You must configure the default cache or an exception will be thrown. It is also advisable to configure a default
     * TTL, as it's unlikely that you'll want paginated items cached forever.
     *
     * The cache key prefix should be unique to the data set you are paginating. Cache key collisions are not considered
     * so you are responsible for providing something that will prevent the cache from returning incorrect data across
     * multiple paginators.
     *
     * @template TKey of array-key
     * @template TValue
     * @param AdapterInterface<TKey, TValue> $adapter
     * @param non-empty-string $cacheKeyPrefix
     * @return Paginator<TKey, TValue>
     * @throws RuntimeException If a default cache has not been configured.
     */
    public function withCachingAdapter(AdapterInterface $adapter, string $cacheKeyPrefix): Paginator
    {
        if ($this->defaults->defaultCache === null) {
            throw new RuntimeException(
                'You cannot generate caching paginators without configuring the cache service '
                . 'under `paginators.defaultCache`',
            );
        }

        return $this->buildAdapter(
            CachingAdapter::class,
            [
                'adapter' => $adapter,
                'prefix'  => $cacheKeyPrefix,
                'cache'   => $this->defaults->defaultCache,
                'ttl'     => $this->defaults->defaultCacheTtl,
            ],
        );
    }

    /**
     * Generate a paginator containing the given items using defaults for page size etc
     *
     * @template TKey of array-key
     * @template TValue
     * @param iterable<TKey, TValue> $items
     * @return Paginator<TKey, TValue>
     */
    public function withItems(iterable $items): Paginator
    {
        $adapter = is_array($items)
            ? new ArrayAdapter($items)
            : new ArrayAdapter(iterator_to_array($items));

        return $this->withAdapter($adapter);
    }

    /**
     * Generate a paginator by configuring an adapter with the given arguments
     *
     * @param string $adapter The service id of an adapter such as `"Array"`, or `"Callback"`
     * @param array<array-key, mixed> $options Adapter options
     */
    public function buildAdapter(string $adapter, array $options = []): Paginator
    {
        $instance = $this->pluginManager->build($adapter, $options);
        if ($instance instanceof AdapterAggregateInterface) {
            $instance = $instance->getPaginatorAdapter();
        }

        assert($instance instanceof AdapterInterface);

        return $this->withAdapter($instance);
    }
}
