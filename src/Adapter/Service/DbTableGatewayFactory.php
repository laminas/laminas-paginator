<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\ServiceManager\MutableCreationOptionsInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DbTableGatewayFactory implements
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
     * @return DbTableGateway
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DbTableGateway(
            $this->creationOptions[0],
            isset($this->creationOptions[1]) ? $this->creationOptions[1] : null,
            isset($this->creationOptions[2]) ? $this->creationOptions[2] : null,
            isset($this->creationOptions[3]) ? $this->creationOptions[3] : null,
            isset($this->creationOptions[4]) ? $this->creationOptions[4] : null
        );
    }
}
