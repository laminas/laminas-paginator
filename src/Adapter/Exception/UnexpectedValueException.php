<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Paginator\Adapter\Exception;

use Zend\Paginator\Exception;

class UnexpectedValueException extends Exception\UnexpectedValueException implements
    ExceptionInterface
{}
