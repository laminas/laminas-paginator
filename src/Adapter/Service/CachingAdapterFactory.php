<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Service;

use DateInterval;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\CachingAdapter;
use Laminas\Paginator\Defaults;
use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

final readonly class CachingAdapterFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): CachingAdapter {
        $defaults = $container->get(Defaults::class);

        if (! is_array($options) || $options === []) {
            throw new InvalidArgumentException('The caching adapter requires non-empty options');
        }

        $adapter = $options['adapter'] ?? null;

        if (! $adapter instanceof AdapterInterface) {
            throw new InvalidArgumentException(
                'An adapter must be supplied under the `adapter` option key',
            );
        }

        $cache = $options['cache'] ?? $defaults->defaultCache;
        if (! $cache instanceof CacheItemPoolInterface) {
            throw new InvalidArgumentException(
                'A cache pool was not given under the `cache` option key and a default has not been configured',
            );
        }

        /** @psalm-var mixed $ttl */
        $ttl = $options['ttl'] ?? $defaults->defaultCacheTtl ?? null;
        if (is_int($ttl) && $ttl > 0) {
            $ttl = new DateInterval(sprintf('PT%dS', $ttl));
        }

        if (is_string($ttl)) {
            $ttl = new DateInterval($ttl);
        }

        if (! $ttl instanceof DateInterval && $ttl !== null) {
            throw new InvalidArgumentException(sprintf(
                'The cache TTL must resolve to a positive date interval. Received "%s"',
                get_debug_type($ttl),
            ));
        }

        $prefix = $options['prefix'] ?? null;
        if (! is_string($prefix) || $prefix === '') {
            throw new InvalidArgumentException(
                'The cache key prefix under the `prefix` option key must be a non-empty string',
            );
        }

        return new CachingAdapter(
            $adapter,
            $prefix,
            $cache,
            $ttl,
        );
    }
}
