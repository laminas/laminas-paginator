<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\Service;

use Interop\Container\ContainerInterface;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function sprintf;

/**
 * @deprecated 2.10.0 Use the adapters in laminas/laminas-paginator-adapter-laminasdb.
 */
class DbTableGatewayFactory implements FactoryInterface
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
     * @return DbTableGateway
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if (null === $options || empty($options)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires a minimum of a laminas-db TableGateway instance',
                DbTableGateway::class
            ));
        }

        return new $requestedName(
            $options[0],
            $options[1] ?? null,
            $options[2] ?? null,
            $options[3] ?? null,
            $options[4] ?? null
        );
    }

    /**
     * Create and return a DbTableGateway instance (v2)
     *
     * @param null|string $name
     * @param string $requestedName
     * @return DbTableGateway
     */
    public function createService(
        ServiceLocatorInterface $container,
        $name = null,
        $requestedName = DbTableGateway::class
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
