<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Iterator;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
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

            // Skipping as these have required arguments
            if (str_contains($target, '\\Db')) {
                continue;
            }

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

    public function testInstanceOfMatches(): void
    {
        $manager    = $this->getPluginManager();
        $reflection = new ReflectionProperty($manager, 'instanceOf');
        $this->assertEquals(AdapterInterface::class, $reflection->getValue($manager), 'instanceOf does not match');
    }

    public function testShareByDefaultAndSharedByDefault(): void
    {
        $manager        = $this->getPluginManager();
        $reflection     = new ReflectionClass($manager);
        $shareByDefault = $sharedByDefault = true;

        foreach ($reflection->getProperties() as $prop) {
            if ($prop->getName() === 'shareByDefault') {
                /** @psalm-var mixed $shareByDefault */
                $shareByDefault = $prop->getValue($manager);
                self::assertIsBool($shareByDefault);
            }
            if ($prop->getName() === 'sharedByDefault') {
                /** @psalm-var mixed $sharedByDefault */
                $sharedByDefault = $prop->getValue($manager);
                self::assertIsBool($sharedByDefault);
            }
        }

        $this->assertSame(
            $shareByDefault,
            $sharedByDefault,
            'Values of shareByDefault and sharedByDefault do not match'
        );
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
