<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Exception;

use Laminas\Paginator\Exception;

/**
 * @deprecated Since 2.22.0 This exception type will be removed in 3.0 without replacement
 *
 * @final
 */
class UnexpectedValueException extends Exception\UnexpectedValueException implements
    ExceptionInterface
{
}
