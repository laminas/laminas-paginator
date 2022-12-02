<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

use ReturnTypeWillChange;

use function call_user_func;

/**
 * @template-covariant TKey of int
 * @template-covariant TValue
 * @implements AdapterInterface<TKey, TValue>
 */
class Callback implements AdapterInterface
{
    /**
     * Callback to be executed to retrieve the items for a page.
     *
     * @var callable(int, int): iterable<TKey, TValue>
     */
    protected $itemsCallback;

    /**
     * Callback to be executed to retrieve the total number of items.
     *
     * @var callable(): int
     */
    protected $countCallback;

    /**
     * Constructs instance.
     *
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
    public function getItems($offset, $itemCountPerPage)
    {
        return call_user_func($this->itemsCallback, $offset, $itemCountPerPage);
    }

    /**
     * Returns the total number of items.
     *
     * Executes the {$countCallback}.
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return call_user_func($this->countCallback);
    }
}
