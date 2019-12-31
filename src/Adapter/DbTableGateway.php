<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Paginator\Adapter;

use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;

class DbTableGateway extends DbSelect
{
    /**
     * Constructs instance.
     *
     * @param TableGateway                $tableGateway
     * @param Where|\Closure|string|array $where
     * @param null                        $order
     */
    public function __construct(TableGateway $tableGateway, $where = null, $order = null)
    {
        $select = $tableGateway->getSql()->select();
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }

        $dbAdapter          = $tableGateway->getAdapter();
        $resultSetPrototype = $tableGateway->getResultSetPrototype();

        parent::__construct($select, $dbAdapter, $resultSetPrototype);
    }
}
