<?php

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
