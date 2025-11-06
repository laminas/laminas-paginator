<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\Service\NullFillFactory;
use Laminas\Paginator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class NullFillFactoryTest extends TestCase
{
    public function testCountOptionMustBeAnInteger(): void
    {
        $factory = new NullFillFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The NullFill adapter requires an integer as its first argument');

        $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
            ['bar'],
        );
    }

    public function testAdapterCreation(): void
    {
        $factory = new NullFillFactory();
        $adapter = $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
            [3],
        );

        self::assertSame([null, null, null], $adapter->getItems(0, 5));
    }
}
