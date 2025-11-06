<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use Countable;
use Iterator as SplIterator;
use Laminas\Paginator\SerializableLimitIterator;

use function count;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements AdapterInterface<TKey, TValue>
 */
final readonly class Iterator implements AdapterInterface
{
    /**
     * Iterator which implements Countable
     *
     * @var (SplIterator<TKey, TValue>)&Countable
     */
    private SplIterator $iterator;

    private int $count;

    /**
     * @param SplIterator<TKey, TValue> $iterator Iterator to paginate
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(SplIterator $iterator)
    {
        if (! $iterator instanceof Countable) {
            throw new Exception\InvalidArgumentException('Iterator must implement Countable');
        }

        $this->iterator = $iterator;
        $this->count    = count($iterator);
    }

    /** @return SerializableLimitIterator<TKey, TValue> */
    public function getItems(int $offset, int $itemCountPerPage): iterable
    {
        return new SerializableLimitIterator($this->iterator, $offset, $itemCountPerPage);
    }

    public function count(): int
    {
        return $this->count;
    }
}
