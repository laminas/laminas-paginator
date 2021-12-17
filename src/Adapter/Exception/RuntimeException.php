<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Exception;

use Laminas\Paginator\Exception;

class RuntimeException extends Exception\RuntimeException implements
    ExceptionInterface
{
}
