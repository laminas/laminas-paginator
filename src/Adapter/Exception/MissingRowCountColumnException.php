<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

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
