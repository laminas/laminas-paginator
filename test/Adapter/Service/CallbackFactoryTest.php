<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\Service\CallbackFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class CallbackFactoryTest extends TestCase
{
    public function testOptionsAreRequired(): void
    {
        $factory = new CallbackFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('requires that at least two options');

        $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
        );
    }

    public function testAdapterCreation(): void
    {
        $factory = new CallbackFactory();
        $adapter = $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
            [
                static fn (): array => [1, 2, 3],
                static fn (): int => 3,
            ],
        );

        self::assertSame([1, 2, 3], $adapter->getItems(0, 5));
    }
}
