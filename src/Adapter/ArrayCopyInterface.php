<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter;

interface ArrayCopyInterface
{
    /**
     * @internal
     *
     * @see https://github.com/laminas/laminas-paginator/issues/3 Reference for creating an internal cache ID
     *
     * @todo The next major version should rework the entire caching of a paginator.
     *
     * @return array
     */
    public function getArrayCopy();
}
