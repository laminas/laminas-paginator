<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter;

class Callback implements AdapterInterface
{
    /**
     * Callback to be executed to retrieve the items for a page.
     *
     * @var CallbackHandler
     */
    protected $itemsCallback;

    /**
     * Callback to be executed to retrieve the total number of items.
     *
     * @var CallbackHandler
     */
    protected $countCallback;

    /**
     * Constructs instance.
     *
     * @param callable $itemsCallback Callback to be executed to retrieve the items for a page.
     * @param callable $countCallback Callback to be executed to retrieve the total number of items.
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
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
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
    public function count()
    {
        return call_user_func($this->countCallback);
    }
}
