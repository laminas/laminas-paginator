<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

/**
 * @template-covariant TKey
 * @template-covariant TValue
 * @extends TestAdapter<TKey, TValue>
 */
class TestSimilarAdapter extends TestAdapter
{
    /**
     * @return string
     */
    public function differentFunction()
    {
        return "test";
    }
}
