<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayIterator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\AdapterPluginManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function range;

/**
 * @covers \Laminas\Paginator\AdapterPluginManager
 */
class AdapterPluginManagerTest extends TestCase
{
    /** @var AdapterPluginManager */
    protected $adapterPluginManager;

    protected function setUp(): void
    {
        $this->adapterPluginManager = new AdapterPluginManager(
            $this->createMock(ContainerInterface::class)
        );
    }

    /**
     * Note: does not return expectations for db-based adapters, as they are deprecated.
     *
     * @psalm-return iterable<string, array{
     *   0: string,
     *   1: array<array-key, mixed>,
     *   2: class-string
     * }>
     */
    public static function pluginProvider(): iterable
    {
        yield 'array-adapter'    => ['array', [1, 2, 3], Adapter\ArrayAdapter::class];
        yield 'iterator-adapter' => ['iterator', [new ArrayIterator(range(1, 101))], Adapter\Iterator::class];
        yield 'null-adapter'     => ['null', [101], Adapter\NullFill::class];

        $itemsCallback = static fn(): array => [];
        $countCallback = static fn(): int => 0;
        yield 'callback-adapter' => ['callback', [$itemsCallback, $countCallback], Adapter\Callback::class];
    }

    /**
     * @dataProvider pluginProvider
     * @psalm-param class-string $expectedType
     */
    public function testCanRetrieveAdapterPlugin(string $pluginName, array $options, string $expectedType): void
    {
        $plugin = $this->adapterPluginManager->get($pluginName, $options);
        $this->assertInstanceOf($expectedType, $plugin);
    }
}
