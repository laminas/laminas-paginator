<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Exception;

use Laminas\Paginator\Exception;

class InvalidArgumentException extends Exception\InvalidArgumentException implements
    ExceptionInterface
{
}
