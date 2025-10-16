<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

use function assert;
use function class_exists;

final class ScrollingStylePluginManagerCompatibilityTest extends TestCase
{
    public function testInstanceOfMatches(): void
    {
        $manager    = $this->getPluginManager();
        $reflection = new ReflectionProperty($manager, 'instanceOf');
        $this->assertEquals(
            ScrollingStyleInterface::class,
            $reflection->getValue($manager),
            'instanceOf does not match',
        );
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
            }
            if ($prop->getName() === 'sharedByDefault') {
                /** @psalm-var mixed $sharedByDefault */
                $sharedByDefault = $prop->getValue($manager);
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
