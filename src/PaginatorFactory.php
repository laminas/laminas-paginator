<?php

declare(strict_types=1);

namespace Laminas\Paginator;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;

use function assert;
use function is_array;
use function iterator_to_array;

final readonly class PaginatorFactory
{
    public function __construct(
        private AdapterPluginManager $pluginManager,
        private Defaults $defaults,
    ) {
    }

    /**
     * Generate a Paginator using the given adapter with configured defaults for page size etc.
     *
     * @template TKey of array-key
     * @template TValue
     * @param AdapterInterface<TKey, TValue> $adapter
     * @return Paginator<TKey, TValue>
     */
    public function withAdapter(AdapterInterface $adapter): Paginator
    {
        return new Paginator(
            $adapter,
            $this->defaults->itemCountPerPage,
            $this->defaults->pageRange,
            $this->defaults->scrollingStyle,
        );
    }

    /**
     * Generate a paginator containing the given items using defaults for page size etc
     *
     * @template TKey of array-key
     * @template TValue
     * @param iterable<TKey, TValue> $items
     * @return Paginator<TKey, TValue>
     */
    public function withItems(iterable $items): Paginator
    {
        $adapter = is_array($items)
            ? new ArrayAdapter($items)
            : new ArrayAdapter(iterator_to_array($items));

        return $this->withAdapter($adapter);
    }

    /**
     * Generate a paginator by configuring an adapter with the given arguments
     *
     * @param string $adapter The service id of an adapter such as `"Array"`, or `"Callback"`
     * @param array<array-key, mixed> $options Adapter options
     */
    public function buildAdapter(string $adapter, array $options = []): Paginator
    {
        $instance = $this->pluginManager->build($adapter, $options);
        if ($instance instanceof AdapterAggregateInterface) {
            $instance = $instance->getPaginatorAdapter();
        }

        assert($instance instanceof AdapterInterface);

        return $this->withAdapter($instance);
    }
}
