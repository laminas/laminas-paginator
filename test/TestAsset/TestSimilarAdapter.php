<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

use Laminas\Paginator\Adapter\AdapterInterface;

class TestSimilarAdapter extends TestAdapter implements AdapterInterface
{
    /**
     * @return string
     */
    public function differentFunction()
    {
        return "test";
    }
}
