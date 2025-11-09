<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayAccess;
use DateInterval;
use Laminas\Paginator\Exception\ExceptionInterface;
use Laminas\Paginator\Exception\RuntimeException;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleFactory;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class DefaultsFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): Defaults {
        $config = $container->has('config')
            ? $container->get('config')
            : [];

        assert(is_array($config) || $config instanceof ArrayAccess);
        /** @var mixed $options */
        $options = $config['paginator'] ?? [];
        assert(is_array($options));

        $itemCount = $options['itemCountPerPage'] ?? 10;
        if (! is_int($itemCount) || $itemCount < 1) {
            throw new RuntimeException(
                'The default item count per page must be a positive integer',
            );
        }

        $range = $options['pageRange'] ?? 10;
        if (! is_int($range) || $range < 1) {
            throw new RuntimeException(
                'The default page range must be a positive integer',
            );
        }

        $style = $options['scrollingStyle'] ?? 'Sliding';
        if (! is_string($style) && ! $style instanceof ScrollingStyleInterface) {
            throw new RuntimeException(sprintf(
                'The default scrolling style must be a string, or a %s instance',
                ScrollingStyleInterface::class,
            ));
        }

        if (is_string($style)) {
            try {
                $instance = ScrollingStyleFactory::fromString($style, false);
            } catch (ExceptionInterface) {
                /** @psalm-var mixed $instance */
                $instance = $container->get($style);
            }

            assert($instance instanceof ScrollingStyleInterface);

            $style = $instance;
        }

        $cache = $options['defaultCache'] ?? null;
        if ($cache !== null && (! is_string($cache) || $cache === '')) {
            throw new RuntimeException(
                'The default cache should be a non-empty string when set',
            );
        }

        if ($cache !== null) {
            /** @psalm-var mixed $cache */
            $cache = $container->get($cache);
            if (! $cache instanceof CacheItemPoolInterface) {
                throw new RuntimeException(sprintf(
                    'The default cache should resolve to an instance of "%s", Received "%s"',
                    CacheItemPoolInterface::class,
                    get_debug_type($cache),
                ));
            }
        }

        /** @var mixed $defaultTtl */
        $defaultTtl = $options['defaultCacheTTL'] ?? null;
        if (is_int($defaultTtl)) {
            $defaultTtl = new DateInterval(sprintf('PT%dS', $defaultTtl));
        }

        if (is_string($defaultTtl) && $defaultTtl !== '') {
            $defaultTtl = new DateInterval($defaultTtl);
        }

        if (! $defaultTtl instanceof DateInterval && $defaultTtl !== null) {
            throw new RuntimeException(sprintf(
                'The default TTL must be configured as a date interval string, integer or null. Received %s',
                get_debug_type($defaultTtl),
            ));
        }

        return new Defaults(
            $itemCount,
            $range,
            $style,
            $cache,
            $defaultTtl,
        );
    }
}
