<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\Callback;
use Laminas\ServiceManager\MutableCreationOptionsInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CallbackFactory implements
    FactoryInterface,
    MutableCreationOptionsInterface
{
    /**
     * Adapter options
     * @var array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * {@inheritDoc}
     *
     * @return Callback
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Callback($this->creationOptions[0], $this->creationOptions[1]);
    }
}
