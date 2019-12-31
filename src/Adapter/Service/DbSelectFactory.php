<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\Service;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DbSelectFactory implements FactoryInterface
{
    /**
     * Adapter options
     * @var array
     */
    protected $creationOptions;

    /**
     * Construct with adapter options
     * @param array $creationOptions
     */
    public function __construct(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Laminas\Navigation\Navigation
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $class = new \ReflectionClass('Laminas\Paginator\Adapter\DbSelect');
        return $class->newInstanceArgs($this->creationOptions);
    }
}
