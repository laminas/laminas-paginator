<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter;

/**
 * @category   Laminas
 * @package    Laminas_Paginator
 */
class Null implements AdapterInterface
{
    /**
     * Item count
     *
     * @var integer
     */
    protected $count = null;

    /**
     * Constructor.
     *
     * @param integer $count Total item count (Optional)
     */
    public function __construct($count = 0)
    {
        $this->count = $count;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($offset >= $this->count()) {
            return array();
        }

        $remainItemCount  = $this->count() - $offset;
        $currentItemCount = $remainItemCount > $itemCountPerPage ? $itemCountPerPage : $remainItemCount;

        return array_fill(0, $currentItemCount, null);
    }

    /**
     * Returns the total number of rows in the array.
     *
     * @return integer
     */
    public function count()
    {
        return $this->count;
    }
}
