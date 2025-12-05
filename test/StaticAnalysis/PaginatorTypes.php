<?php

declare(strict_types=1);

namespace LaminasTest\Paginator\StaticAnalysis;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use stdClass;

/**
 * @psalm-suppress UnusedClass
 */
final readonly class PaginatorTypes
{
    /**
     * @param array<non-empty-string, stdClass> $data
     * @return ArrayAdapter<non-empty-string, stdClass>
     */
    public function arrayAdapterPreservesTypes(
        array $data,
    ): ArrayAdapter {
        return new ArrayAdapter($data);
    }

    /**
     * @param AdapterInterface<non-empty-string, stdClass> $adapter
     * @return Paginator<non-empty-string, stdClass>
     */
    public function paginatorPreservesTypes(AdapterInterface $adapter): Paginator
    {
        return new Paginator(
            $adapter,
        );
    }
}
