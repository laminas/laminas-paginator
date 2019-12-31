<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\DbSelect;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\MutableCreationOptionsInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DbSelectFactory implements
    FactoryInterface,
    MutableCreationOptionsInterface
{
    /**
     * Adapter options
     *
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
     * @return \Laminas\Paginator\Adapter\DbSelect
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DbSelect(
            $this->creationOptions[0],
            $this->creationOptions[1],
            isset($this->creationOptions[2]) ? $this->creationOptions[2] : null
        );
    }
}
