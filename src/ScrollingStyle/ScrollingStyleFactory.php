<?php

declare(strict_types=1);

namespace Laminas\Paginator\ScrollingStyle;

use Laminas\Paginator\Exception\InvalidArgumentException;

use function sprintf;
use function strtolower;

/**
 * @internal
 *
 * @psalm-internal Laminas\Paginator
 * @psalm-internal LaminasTest\Paginator
 */
final readonly class ScrollingStyleFactory
{
    public static function fromString(string $style, bool $useDefault = true): ScrollingStyleInterface
    {
        $instance = match (strtolower($style)) {
            'all' => new All(),
            'elastic' => new Elastic(),
            'jumping' => new Jumping(),
            'sliding' => new Sliding(),
            default => $useDefault ? new Sliding() : null,
        };

        if ($instance === null) {
            throw new InvalidArgumentException(sprintf(
                'The scrolling style "%s" is not known',
                $style,
            ));
        }

        return $instance;
    }
}
