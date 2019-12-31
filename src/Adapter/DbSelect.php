<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;

/**
 * @category   Laminas
 * @package    Laminas_Paginator
 */
class DbSelect implements AdapterInterface
{

    /**
     * @var Sql
     */
    protected $sql = null;

    /**
     * Database query
     *
     * @var Select
     */
    protected $select = null;

    /**
     * @var ResultSet
     */
    protected $resultSetPrototype = null;

    /**
     * Total item count
     *
     * @var integer
     */
    protected $rowCount = null;

    /**
     * Constructor.
     *
     * @param Select $select The select query
     * @param Adapter|Sql $adapterOrSqlObject DB adapter or Sql object
     * @param null|ResultSetInterface $resultSetPrototype
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Select $select, $adapterOrSqlObject, ResultSetInterface $resultSetPrototype = null)
    {
        $this->select = $select;

        if ($adapterOrSqlObject instanceof Adapter) {
            $adapterOrSqlObject = new Sql($adapterOrSqlObject);
        }

        if (!$adapterOrSqlObject instanceof Sql) {
            throw new Exception\InvalidArgumentException(
                '$adapterOrSqlObject must be an instance of Laminas\Db\Adapter\Adapter or Laminas\Db\Sql\Sql'
            );
        }

        $this->sql                = $adapterOrSqlObject;
        $this->resultSetPrototype = ($resultSetPrototype) ?: new ResultSet;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset           Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $select = clone $this->select;
        $select->offset($offset);
        $select->limit($itemCountPerPage);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        if ($this->rowCount !== null) {
            return $this->rowCount;
        }

        $select = clone $this->select;
        $select->reset(Select::COLUMNS);
        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);

        $select->columns(array('c' => new Expression('COUNT(1)')));

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $row       = $result->current();

        $this->rowCount = $row['c'];

        return $this->rowCount;
    }
}
