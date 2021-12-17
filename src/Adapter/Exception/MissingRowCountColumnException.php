<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Exception;

use LogicException;

use function sprintf;

class MissingRowCountColumnException extends LogicException implements ExceptionInterface
{
    /**
     * @param string $columnName Name of row count column.
     * @return self
     */
    public static function forColumn($columnName)
    {
        return new self(sprintf(
            'Unable to determine row count; missing row count column ("%s") in result',
            $columnName
        ));
    }
}
