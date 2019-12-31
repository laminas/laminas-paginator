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

class DbSelect implements AdapterInterface
{
    const ROW_COUNT_COLUMN_NAME = 'C';

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * Database query
     *
     * @var Select
     */
    protected $select;

    /**
     * Database count query
     *
     * @var Select|null
     */
    protected $countSelect;

    /**
     * @var ResultSet
     */
    protected $resultSetPrototype;

    /**
     * Total item count
     *
     * @var int
     */
    protected $rowCount;

    /**
     * Constructor.
     *
     * @param Select $select The select query
     * @param Adapter|Sql $adapterOrSqlObject DB adapter or Sql object
     * @param null|ResultSetInterface $resultSetPrototype
     * @param null|Select $countSelect
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        Select $select,
        $adapterOrSqlObject,
        ResultSetInterface $resultSetPrototype = null,
        Select $countSelect = null
    ) {
        $this->select = $select;
        $this->countSelect = $countSelect;

        if ($adapterOrSqlObject instanceof Adapter) {
            $adapterOrSqlObject = new Sql($adapterOrSqlObject);
        }

        if (! $adapterOrSqlObject instanceof Sql) {
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
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
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

        return iterator_to_array($resultSet);
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return int
     */
    public function count()
    {
        if ($this->rowCount !== null) {
            return $this->rowCount;
        }

        $select = $this->getSelectCount();

        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();
        $row       = $result->current();

        $this->rowCount = (int) $row[self::ROW_COUNT_COLUMN_NAME];

        return $this->rowCount;
    }

    /**
     * Returns select query for count
     *
     * @return Select
     */
    protected function getSelectCount()
    {
        if ($this->countSelect) {
            return $this->countSelect;
        }

        $select = clone $this->select;
        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);

        $countSelect = new Select;

        $countSelect->columns([self::ROW_COUNT_COLUMN_NAME => new Expression('COUNT(1)')]);
        $countSelect->from(['original_select' => $select]);

        return $countSelect;
    }
}
