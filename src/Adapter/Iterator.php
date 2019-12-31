<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Paginator\Adapter;

use Zend\Paginator;

class Iterator implements AdapterInterface
{
    /**
     * Iterator which implements Countable
     *
     * @var Iterator
     */
    protected $iterator = null;

    /**
     * Item count
     *
     * @var integer
     */
    protected $count = null;

    /**
     * Constructor.
     *
     * @param  \Iterator $iterator Iterator to paginate
     * @throws \Zend\Paginator\Adapter\Exception\InvalidArgumentException
     */
    public function __construct(\Iterator $iterator)
    {
        if (!$iterator instanceof \Countable) {
            throw new Exception\InvalidArgumentException('Iterator must implement Countable');
        }

        $this->iterator = $iterator;
        $this->count = count($iterator);
    }

    /**
     * Returns an iterator of items for a page, or an empty array.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array|\Zend\Paginator\SerializableLimitIterator
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->count == 0) {
            return array();
        }
        return new Paginator\SerializableLimitIterator($this->iterator, $offset, $itemCountPerPage);
    }

    /**
     * Returns the total number of rows in the collection.
     *
     * @return integer
     */
    public function count()
    {
        return $this->count;
    }
}
