<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

use function sprintf;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements AdapterInterface<TKey, TValue>
 */
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

    /**
     * @throws InvalidArgumentException If the cache prefix is too long or contains unsupported characters.
     */
    public function getItems(int $offset, int $itemCountPerPage): iterable
    {
        $key  = sprintf('%s-%d-%d', $this->cacheKeyPrefix, $offset, $itemCountPerPage);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            /** @psalm-var iterable<TKey, TValue> */
            return $item->get();
        }

        $items = $this->adapter->getItems($offset, $itemCountPerPage);
        $item->set($items);
        $item->expiresAfter($this->ttl);
        $this->cache->save($item);

        return $items;
    }

    public function count(): int
    {
        return $this->adapter->count();
    }
}
