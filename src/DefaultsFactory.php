<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use ArrayAccess;
use Laminas\Paginator\Exception\ExceptionInterface;
use Laminas\Paginator\Exception\RuntimeException;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleFactory;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;
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

        return new Defaults(
            $itemCount,
            $range,
            $style,
        );
    }
}
