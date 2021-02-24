<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

use Laminas\Paginator\Adapter\DbSelect;

use function range;

class TestDbSelectAdapter extends DbSelect
{
    /**
     * @inheritDoc
     */
    public function count()
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function getItems($pageNumber, $itemCountPerPage)
    {
        return range(1, 10);
    }
}
