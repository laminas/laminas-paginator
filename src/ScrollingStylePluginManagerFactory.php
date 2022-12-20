<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function is_array;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @final
 */
class ScrollingStylePluginManagerFactory implements FactoryInterface
{
    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @var ServiceManagerConfiguration|null
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @param string|null $requestedName
     * @param ServiceManagerConfiguration|null $options
     * @return ScrollingStylePluginManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $options = ! is_array($options) ? [] : $options;
        return new ScrollingStylePluginManager($container, $options);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|null $name
     * @param string|null $requestedName
     * @return ScrollingStylePluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this($container, $requestedName ?: ScrollingStylePluginManager::class, $this->creationOptions);
    }

    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param ServiceManagerConfiguration $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
