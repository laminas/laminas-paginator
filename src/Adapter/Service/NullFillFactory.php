<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\Exception\InvalidArgumentException;
use Laminas\Paginator\Adapter\NullFill;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function array_shift;
use function get_debug_type;
use function is_array;
use function is_int;
use function sprintf;

/**
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class NullFillFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array|null $options = null,
    ): NullFill {
        $count = 0;
        if (is_array($options) && $options !== []) {
            /** @var mixed $count */
            $count = array_shift($options);
        }

        if (! is_int($count)) {
            throw new InvalidArgumentException(sprintf(
                'The NullFill adapter requires an integer as its first argument. Recieved %s',
                get_debug_type($count),
            ));
        }

        return new NullFill($count);
    }
}
