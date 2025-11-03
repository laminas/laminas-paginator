<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\Callback;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function array_shift;
use function assert;
use function count;
use function is_array;
use function is_callable;
use function sprintf;

/**
 * Create and return an instance of the Callback adapter.
 *
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final class CallbackFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): Callback {
        $options = is_array($options) ? $options : [];
        if (count($options) < 2) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that at least two options, an Items and Count callback, be provided; received %d options',
                self::class,
                count($options)
            ));
        }
        $itemsCallback = array_shift($options);
        assert(is_callable($itemsCallback));
        $countCallback = array_shift($options);
        assert(is_callable($countCallback));

        return new Callback($itemsCallback, $countCallback);
    }
}
