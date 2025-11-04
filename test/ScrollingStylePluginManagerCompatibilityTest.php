<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStyle\Sliding;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;
use stdClass;

use function assert;
use function class_exists;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class ScrollingStylePluginManagerCompatibilityTest extends TestCase
{
    public function testReturnsInstancesOfTheExpectedType(): void
    {
        $manager  = $this->getPluginManager();
        $instance = $manager->get(Sliding::class);
        self::assertInstanceOf(ScrollingStyleInterface::class, $instance);
    }

    public function testInstancesAreShared(): void
    {
        $manager  = $this->getPluginManager();
        $instance = $manager->get(Sliding::class);
        self::assertSame($instance, $manager->get(Sliding::class));
    }

    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException(InvalidServiceException::class);
        /** @psalm-suppress InvalidArgument */
        $this->getPluginManager()->setService('test', $this);
    }

    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = $this->getPluginManager([
            'invokables' => [
                'test' => stdClass::class,
            ],
        ]);
        $this->expectException(InvalidServiceException::class);
        $manager->get('test');
    }

    /**
     * @param class-string $expected
     */
    #[DataProvider('aliasProvider')]
    public function testPluginAliasesResolve(string $alias, string $expected): void
    {
        $this->assertInstanceOf($expected, $this->getPluginManager()->get($alias), "Alias '$alias' does not resolve'");
    }

    /**
     * @return iterable<string, array{0: string, 1: class-string}>
     */
    public static function aliasProvider(): iterable
    {
        $reflection = new ReflectionClassConstant(ScrollingStylePluginManager::class, 'DEFAULT_CONFIG');
        $aliases    = $reflection->getValue()['aliases'] ?? [];
        self::assertIsArray($aliases);
        foreach ($aliases as $alias => $expected) {
            self::assertIsString($alias);
            self::assertIsString($expected);
            assert(class_exists($expected));

            yield $alias => [$alias, $expected];
        }
    }

    /** @param ServiceManagerConfiguration $config */
    protected static function getPluginManager(array $config = []): ScrollingStylePluginManager
    {
        return new ScrollingStylePluginManager(new ServiceManager(), $config);
    }
}
