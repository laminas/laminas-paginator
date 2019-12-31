<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Paginator\TestAsset;

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @subpackage UnitTests
 */
class TestAdapter extends \ArrayObject implements \Zend\Paginator\Adapter\AdapterInterface
{
    public function count()
    {
        return 10;
    }

    public function getItems($pageNumber, $itemCountPerPage)
    {
        return new \ArrayObject(range(1, 10));
    }
}
