<?php

declare(strict_types=1);

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
