<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\Service;

use Interop\Container\ContainerInterface;
use Iterator;
use Laminas\Paginator\Iterator as IteratorAdapter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class IteratorFactory implements FactoryInterface
{
    /**
     * Options to use when creating adapter (v2)
     *
     * @var null|array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return IteratorAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (null === $options || empty($options)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires a minimum of an Iterator instance',
                IteratorAdapter::class
            ));
        }

        $iterator = array_shift($options);

        if (! $iterator instanceof Iterator) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires an Iterator instance; received %s',
                IteratorAdapter::class,
                (is_object($iterator) ? get_class($iterator) : gettype($iterator))
            ));
        }

        return new $requestedName($iterator);
    }

    /**
     * Create and return an IteratorAdapter instance (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param null|string $name
     * @param string $requestedName
     * @return IteratorAdapter
     */
    public function createService(
        ServiceLocatorInterface $container,
        $name = null,
        $requestedName = IteratorAdapter::class
    ) {
        return $this($container, $requestedName, $this->creationOptions);
    }

    /**
     * Options to use with factory (v2)
     *
     * @param array $creationOptions
     * @return void
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }
}
