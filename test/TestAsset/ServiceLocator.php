<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

// phpcs:ignore
abstract class ServiceLocator implements ServiceLocatorInterface, ContainerInterface
{
}
