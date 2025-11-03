<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use function call_user_func;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements AdapterInterface<TKey, TValue>
 */
final class Callback implements AdapterInterface
{
    /**
     * Callback to be executed to retrieve the items for a page.
     *
     * @var callable(int, int): iterable<TKey, TValue>
     */
    private $itemsCallback;

    /**
     * Callback to be executed to retrieve the total number of items.
     *
     * @var callable(): int
     */
    private $countCallback;

    /**
     * @param callable(int, int): iterable<TKey, TValue> $itemsCallback Callback to be executed to retrieve
     *                                                            the items for a page.
     * @param callable(): int $countCallback Callback to be executed to retrieve the total number of items.
     */
    public function __construct(callable $itemsCallback, callable $countCallback)
    {
        $this->itemsCallback = $itemsCallback;
        $this->countCallback = $countCallback;
    }

    /**
     * Returns an array of items for a page.
     *
     * Executes the {$itemsCallback}.
     *
     * @inheritDoc
     */
    public function getItems(int $offset, int $itemCountPerPage): iterable
    {
        return call_user_func($this->itemsCallback, $offset, $itemCountPerPage);
    }

    public function count(): int
    {
        return call_user_func($this->countCallback);
    }
}
