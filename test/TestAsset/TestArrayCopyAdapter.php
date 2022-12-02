<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\TestAsset;

/**
 * @template-covariant TKey
 * @template-covariant TValue
 * @extends TestAdapter<TKey, TValue>
 */
class TestArrayCopyAdapter extends TestAdapter
{
    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [];
    }
}
