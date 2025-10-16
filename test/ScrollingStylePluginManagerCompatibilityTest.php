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
use ReflectionProperty;
use stdClass;

use function assert;
use function class_exists;

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

    /**
     * @return list<array{0: string, 1: class-string}>
     */
    public static function aliasProvider(): array
    {
        $manager    = self::getPluginManager();
        $reflection = new ReflectionProperty($manager, 'aliases');
        $data       = [];
        foreach ($reflection->getValue($manager) as $alias => $expected) {
            self::assertIsString($alias);
            self::assertIsString($expected);
            assert(class_exists($expected));

            $data[] = [$alias, $expected];
        }

        return $data;
    }

    protected static function getPluginManager(): ScrollingStylePluginManager
    {
        return new ScrollingStylePluginManager(new ServiceManager());
    }
}
