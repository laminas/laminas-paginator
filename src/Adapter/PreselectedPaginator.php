<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter;

class PreselectedPaginator implements AdapterInterface
{
    /** @var iterable */
    private $items;

    /** @var int */
    private $count;

    public function __construct(iterable $items = [], int $count = 0)
    {
        $this->items = $items;
        $this->count = $count;
    }

    /**
     * @inheritDoc
     */
    public function getItems($offset, $itemCountPerPage): iterable
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->count;
    }
}
