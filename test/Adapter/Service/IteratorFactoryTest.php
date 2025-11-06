<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter\Service;

use ArrayIterator;
use Laminas\Paginator\Adapter\Service\IteratorFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function iterator_to_array;

final class IteratorFactoryTest extends TestCase
{
    public function testOptionsAreRequired(): void
    {
        $factory = new IteratorFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('requires a minimum of an Iterator instance');

        $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
        );
    }

    public function testOptionMustBeAnIterator(): void
    {
        $factory = new IteratorFactory();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('requires an Iterator instance; received ');

        $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
            [(object) []],
        );
    }

    public function testAdapterCreation(): void
    {
        $factory = new IteratorFactory();
        $adapter = $factory->__invoke(
            $this->createMock(ContainerInterface::class),
            'foo',
            [new ArrayIterator([1, 2, 3])],
        );

        self::assertSame(
            [1, 2, 3],
            iterator_to_array($adapter->getItems(0, 5)),
        );
    }
}
