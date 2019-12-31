<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Paginator\TestAsset;

use Zend\Paginator;
use Zend\Paginator\Adapter;

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @subpackage UnitTests
 */
class TestArrayAggregate implements Paginator\AdapterAggregateInterface
{
    public function getPaginatorAdapter()
    {
        return new Adapter\ArrayAdapter(array(1, 2, 3, 4));
    }
}
