<?php

declare(strict_types=1);

namespace Laminas\Paginator\Adapter\Service;

use Laminas\Paginator\Adapter\Callback;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;

use function array_shift;
use function count;
use function is_array;
use function sprintf;

/**
 * Create and return an instance of the Callback adapter.
 */
class CallbackFactory implements FactoryInterface
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
     * @return Callback
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = is_array($options) ? $options : [];
        if (count($options) < 2) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that at least two options, an Items and Count callback, be provided; received %d options',
                self::class,
                count($options)
            ));
        }
        $itemsCallback = array_shift($options);
        $countCallback = array_shift($options);
        return new Callback($itemsCallback, $countCallback);
    }

    /**
     * Create and return a Callback instance (v2)
     *
     * @param null|string $name
     * @param string $requestedName
     * @return Callback
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = Callback::class)
    {
        return $this($container, $requestedName, $this->creationOptions);
    }

    /**
     * Options to use with factory (v2)
     *
     * @return void
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }
}
