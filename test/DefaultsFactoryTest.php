<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\ConfigProvider;
use Laminas\Paginator\DefaultsFactory;
use Laminas\Paginator\Exception\RuntimeException;
use Laminas\Paginator\ScrollingStyle\Jumping;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStyle\Sliding;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

use function array_replace_recursive;
use function sprintf;

final class DefaultsFactoryTest extends TestCase
{
    private function containerWithConfig(array $config = []): ServiceManager
    {
        $config = array_replace_recursive(
            (new ConfigProvider())->__invoke(),
            $config,
        );

        /** @psalm-suppress MixedArrayAssignment Life is too short */
        $config['dependencies']['services']['config'] = $config;

        /** @psalm-suppress MixedArgument */
        return new ServiceManager($config['dependencies']);
    }

    public function testDefaultsCanBeCreatedWhenThereIsNoConfig(): void
    {
        $factory  = new DefaultsFactory();
        $defaults = $factory(new ServiceManager(), 'foo');
        self::assertSame(10, $defaults->itemCountPerPage);
        self::assertSame(10, $defaults->pageRange);
        self::assertInstanceOf(Sliding::class, $defaults->scrollingStyle);
    }

    public function testDefaultsCanBeConfigured(): void
    {
        $factory   = new DefaultsFactory();
        $container = $this->containerWithConfig([
            'paginator' => [
                'itemCountPerPage' => 5,
                'pageRange'        => 6,
                'scrollingStyle'   => 'Jumping',
            ],
        ]);

        $defaults = $factory($container, 'foo');
        self::assertSame(5, $defaults->itemCountPerPage);
        self::assertSame(6, $defaults->pageRange);
        self::assertInstanceOf(Jumping::class, $defaults->scrollingStyle);
    }

    /** @return list<array{0: mixed}> */
    public static function notPositiveIntegers(): array
    {
        return [
            ['not an integer'],
            ['2'],
            [''],
            [-10],
        ];
    }

    #[DataProvider('notPositiveIntegers')]
    public function testPageSizeMustBePositiveInt(mixed $pageSize): void
    {
        $factory   = new DefaultsFactory();
        $container = $this->containerWithConfig([
            'paginator' => [
                'itemCountPerPage' => $pageSize,
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The default item count per page must be a positive integer');

        $factory($container, 'foo');
    }

    #[DataProvider('notPositiveIntegers')]
    public function testPageRangeMustBePositiveInt(mixed $pageRange): void
    {
        $factory   = new DefaultsFactory();
        $container = $this->containerWithConfig([
            'paginator' => [
                'pageRange' => $pageRange,
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The default page range must be a positive integer');

        $factory($container, 'foo');
    }

    /** @return list<array{0: mixed}> */
    public static function invalidScrollingStyles(): array
    {
        return [
            [1],
            [(object) []],
            [[]],
        ];
    }

    #[DataProvider('invalidScrollingStyles')]
    public function testInvalidScrollingStylesAreExceptional(mixed $style): void
    {
        $factory   = new DefaultsFactory();
        $container = $this->containerWithConfig([
            'paginator' => [
                'scrollingStyle' => $style,
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The default scrolling style must be a string, or a %s instance',
            ScrollingStyleInterface::class,
        ));

        $factory($container, 'foo');
    }

    public function testUnknownScrollingStylesWillBeFetchedFromTheContainer(): void
    {
        $style     = $this->createMock(ScrollingStyleInterface::class);
        $factory   = new DefaultsFactory();
        $container = $this->containerWithConfig([
            'paginator'    => [
                'scrollingStyle' => 'foo',
            ],
            'dependencies' => [
                'factories' => [
                    'foo' => fn (): ScrollingStyleInterface => $style,
                ],
            ],
        ]);

        $defaults = $factory($container, 'bar');
        self::assertSame($style, $defaults->scrollingStyle);
    }

    public function testContainerExceptionsPropagateWhenStyleCannotBeRetrievedFromTheContainer(): void
    {
        $factory   = new DefaultsFactory();
        $container = $this->containerWithConfig([
            'paginator' => [
                'scrollingStyle' => 'foo',
            ],
        ]);

        $this->expectException(NotFoundExceptionInterface::class);
        $factory($container, 'bar');
    }
}
