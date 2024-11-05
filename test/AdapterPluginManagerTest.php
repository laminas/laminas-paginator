<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use ArrayIterator;
use Laminas\Paginator\Adapter;
use Laminas\Paginator\AdapterPluginManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function range;

#[CoversClass(AdapterPluginManager::class)]
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
     * @psalm-param class-string $expectedType
     */
    #[DataProvider('pluginProvider')]
    public function testCanRetrieveAdapterPlugin(string $pluginName, array $options, string $expectedType): void
    {
        $plugin = $this->adapterPluginManager->get($pluginName, $options);
        $this->assertInstanceOf($expectedType, $plugin);
    }
}
