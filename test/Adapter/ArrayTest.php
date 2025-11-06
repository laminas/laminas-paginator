<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\Adapter;

use Laminas\Paginator\Adapter\ArrayAdapter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;
use function range;

final class ArrayTest extends TestCase
{
    private ArrayAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new ArrayAdapter(range(1, 101));
    }

    public function testGetsItemsAtOffsetZero(): void
    {
        $expected = range(1, 10);

        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen(): void
    {
        $expected = range(11, 20);

        $actual = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount(): void
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    #[Group('Laminas-4151')]
    public function testEmptySet(): void
    {
        $adapter = new ArrayAdapter([]);
        $actual  = $adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }

    public function testBasicBehaviourWithStringKeys(): void
    {
        $adapter = new ArrayAdapter([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
        ]);

        self::assertCount(3, $adapter);

        self::assertSame(
            ['a' => 'a'],
            iterator_to_array($adapter->getItems(0, 1)),
        );

        self::assertSame(
            ['b' => 'b'],
            iterator_to_array($adapter->getItems(1, 1)),
        );

        self::assertSame(
            ['c' => 'c'],
            iterator_to_array($adapter->getItems(2, 1)),
        );

        self::assertSame(
            [
                'a' => 'a',
                'b' => 'b',
            ],
            iterator_to_array($adapter->getItems(0, 2)),
        );
    }
}
