<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;

/**
 * Interface that aggregates a Laminas\Paginator\Adapter\Abstract just like IteratorAggregate does for Iterators.
 */
interface AdapterAggregateInterface
{
    /**
     * Return a fully configured Paginator Adapter from this method.
     *
     * @return AdapterInterface
     */
    public function getPaginatorAdapter();
}
