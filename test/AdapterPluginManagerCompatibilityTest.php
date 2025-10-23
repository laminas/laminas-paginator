<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Iterator;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function assert;
use function class_exists;
use function is_string;
use function str_contains;

final class AdapterPluginManagerCompatibilityTest extends TestCase
{
    protected static function getPluginManager(): AdapterPluginManager
    {
        return new AdapterPluginManager(new ServiceManager());
    }

    /**
     * @return iterable<string, array{0: string, 1: class-string}>
     */
    public static function aliasProvider(): iterable
    {
        $pluginManager = self::getPluginManager();
        $r             = new ReflectionProperty($pluginManager, 'aliases');
        $aliases       = $r->getValue($pluginManager);
        self::assertIsArray($aliases);

        foreach ($aliases as $alias => $target) {
            assert(is_string($alias) && is_string($target));

            // Skipping as has required arguments
            if (str_contains($target, '\\Callback')) {
                continue;
            }

            // Skipping as has required arguments
            if (str_contains($target, Iterator::class)) {
                continue;
            }

            assert(class_exists($target));

            yield $alias => [$alias, $target];
        }
    }

    public function testInstancesAreTheExpectedType(): void
    {
        $manager  = $this->getPluginManager();
        $instance = $manager->get(ArrayAdapter::class);
        self::assertInstanceOf(AdapterInterface::class, $instance);
    }

    public function testAdaptersAreShared(): void
    {
        $manager  = $this->getPluginManager();
        $instance = $manager->get(ArrayAdapter::class);
        self::assertInstanceOf(ArrayAdapter::class, $instance);
        self::assertSame($instance, $manager->get(ArrayAdapter::class));
    }

    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException(InvalidServiceException::class);
        /** @psalm-suppress InvalidArgument */
        $this->getPluginManager()->setService('test', $this);
    }

    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', stdClass::class);
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
}
