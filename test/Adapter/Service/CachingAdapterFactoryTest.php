<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter\Service;

use DateInterval;
use Exception;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Adapter\CachingAdapter;
use Laminas\Paginator\Adapter\Service\CachingAdapterFactory;
use Laminas\Paginator\ConfigProvider;
use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionProperty;
use Throwable;

use function array_replace_recursive;

final class CachingAdapterFactoryTest extends TestCase
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

    /** @return list<array{0: null|array}> */
    public static function missingOptions(): array
    {
        return [
            [null],
            [[]],
        ];
    }

    #[DataProvider('missingOptions')]
    public function testOptionsMustBeSpecified(array|null $options): void
    {
        $container = $this->containerWithConfig();
        $factory   = new CachingAdapterFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The caching adapter requires non-empty options');

        $factory->__invoke($container, 'foo', $options);
    }

    public function testAnAdapterToWrapIsARequiredOption(): void
    {
        $container = $this->containerWithConfig();
        $factory   = new CachingAdapterFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An adapter must be supplied under the `adapter` option key');

        $factory->__invoke($container, 'foo', ['adapter' => null]);
    }

    public function testACacheMustBeConfigured(): void
    {
        $container = $this->containerWithConfig();
        $factory   = new CachingAdapterFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'A cache pool was not given under the `cache` option key and a default has not been configured',
        );

        $factory->__invoke($container, 'foo', ['adapter' => new ArrayAdapter([])]);
    }

    /** @return list<array{0: mixed}> */
    public static function invalidPrefixes(): array
    {
        return [
            [''],
            [null],
            [10],
        ];
    }

    #[DataProvider('invalidPrefixes')]
    public function testANonEmptyPrefixMustBeConfigured(mixed $prefix): void
    {
        $container = $this->containerWithConfig();
        $factory   = new CachingAdapterFactory();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The cache key prefix under the `prefix` option key must be a non-empty string',
        );

        $factory->__invoke($container, 'foo', [
            'adapter' => new ArrayAdapter([]),
            'cache'   => $this->createMock(CacheItemPoolInterface::class),
            'prefix'  => $prefix,
        ]);
    }

    /** @return list<array{0: mixed, 1: class-string<Throwable>}> */
    public static function invalidCacheTtl(): array
    {
        return [
            ['', Exception::class],
            ['Kermit', Exception::class],
            [-10, InvalidArgumentException::class],
            [(object) [], InvalidArgumentException::class],
        ];
    }

    /** @param class-string<Throwable> $expectException */
    #[DataProvider('invalidCacheTtl')]
    public function testInvalidTtlValues(mixed $ttl, string $expectException): void
    {
        $container = $this->containerWithConfig();
        $factory   = new CachingAdapterFactory();
        $this->expectException($expectException);

        $factory->__invoke($container, 'foo', [
            'adapter' => new ArrayAdapter([]),
            'cache'   => $this->createMock(CacheItemPoolInterface::class),
            'prefix'  => 'foo',
            'ttl'     => $ttl,
        ]);
    }

    /** @return list<array{0: mixed, 1: DateInterval|null}> */
    public static function validTtlValues(): array
    {
        return [
            [null, null],
            [new DateInterval('PT10S'), new DateInterval('PT10S')],
            ['PT10S', new DateInterval('PT10S')],
            [10, new DateInterval('PT10S')],
        ];
    }

    #[DataProvider('validTtlValues')]
    public function testValidTtl(mixed $ttl, DateInterval|null $expect): void
    {
        $container = $this->containerWithConfig();
        $factory   = new CachingAdapterFactory();

        $adapter = $factory->__invoke($container, 'foo', [
            'adapter' => new ArrayAdapter([]),
            'cache'   => $this->createMock(CacheItemPoolInterface::class),
            'prefix'  => 'foo',
            'ttl'     => $ttl,
        ]);

        $this->assertTtlMatches($adapter, $expect);
    }

    private function assertTtlMatches(CachingAdapter $adapter, DateInterval|null $expect): void
    {
        $reflectionProperty = new ReflectionProperty($adapter, 'ttl');
        /** @psalm-var mixed $value */
        $value = $reflectionProperty->getValue($adapter);

        self::assertEquals($expect, $value);
    }
}
