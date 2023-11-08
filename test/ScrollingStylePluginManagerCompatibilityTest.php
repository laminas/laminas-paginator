<?php

declare(strict_types=1);

namespace LaminasTest\Paginator;

use Laminas\Paginator\Exception\InvalidArgumentException;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\Paginator\ScrollingStylePluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;

class ScrollingStylePluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @return ScrollingStylePluginManager
     */
    protected static function getPluginManager()
    {
        return new ScrollingStylePluginManager(new ServiceManager());
    }

    /**
     * @return string
     */
    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    /**
     * @return string
     */
    protected function getInstanceOf()
    {
        return ScrollingStyleInterface::class;
    }
}
