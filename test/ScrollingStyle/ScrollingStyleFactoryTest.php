<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\ScrollingStyle;

use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\Paginator\ScrollingStyle\All;
use Laminas\Paginator\ScrollingStyle\Elastic;
use Laminas\Paginator\ScrollingStyle\Jumping;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleFactory;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStyle\Sliding;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ScrollingStyleFactoryTest extends TestCase
{
    public function testExceptionThrownForInvalidArgumentWhenUseDefaultIsFalse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The scrolling style "muppets" is not known');

        ScrollingStyleFactory::fromString('muppets', false);
    }

    public function testTheHardCodedDefaultStyleIsReturnedWhenGivenInvalidString(): void
    {
        $style = ScrollingStyleFactory::fromString('muppets', true);

        self::assertInstanceOf(Sliding::class, $style);
    }

    /** @return list<array{0: string, 1: class-string<ScrollingStyleInterface>}> */
    public static function styleStringArguments(): array
    {
        return [
            ['sliding', Sliding::class],
            ['SLIDING', Sliding::class],
            ['jumping', Jumping::class],
            ['JUMPING', Jumping::class],
            ['all', All::class],
            ['All', All::class],
            ['elastic', Elastic::class],
            ['ELASTIC', Elastic::class],
            ['foo', Sliding::class],
            ['FOO', Sliding::class],
            ['', Sliding::class],
        ];
    }

    /** @param class-string<ScrollingStyleInterface> $expect */
    #[DataProvider('styleStringArguments')]
    public function testExpectedInstanceTypes(string $argument, string $expect): void
    {
        $style = ScrollingStyleFactory::fromString($argument, true);

        self::assertInstanceOf($expect, $style);
    }
}
