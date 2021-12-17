<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Exception;

use Laminas\Paginator\Exception;

class UnexpectedValueException extends Exception\UnexpectedValueException implements
    ExceptionInterface
{
}
