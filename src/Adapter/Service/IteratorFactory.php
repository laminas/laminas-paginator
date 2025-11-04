<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Service;

use Iterator;
use Laminas\Paginator\Adapter\Iterator as IteratorAdapter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function array_shift;
use function get_debug_type;
use function sprintf;

/**
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final class IteratorFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): IteratorAdapter {
        if (null === $options || $options === []) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires a minimum of an Iterator instance',
                IteratorAdapter::class
            ));
        }

        $iterator = array_shift($options);

        if (! $iterator instanceof Iterator) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires an Iterator instance; received %s',
                IteratorAdapter::class,
                get_debug_type($iterator)
            ));
        }

        /** @psalm-var Iterator<array-key, mixed> $iterator */

        return new IteratorAdapter($iterator);
    }
}
