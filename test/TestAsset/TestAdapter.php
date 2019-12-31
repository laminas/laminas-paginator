<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

/**
 * @category   Laminas
 * @package    Laminas_Paginator
 * @subpackage UnitTests
 */
class TestAdapter extends \ArrayObject implements \Laminas\Paginator\Adapter\AdapterInterface
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
