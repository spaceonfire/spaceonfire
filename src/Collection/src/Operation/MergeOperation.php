<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Operation;

/**
 * @template K of array-key
 * @template V
 * @extends AbstractOperation<K,V,K,V>
 */
final class MergeOperation extends AbstractOperation
{
    /**
     * @var array<iterable<K,V>>
     */
    private array $collections;

    /**
     * @param bool $preserveKeys
     * @param iterable<K,V> ...$collections
     */
    public function __construct(bool $preserveKeys = false, iterable ...$collections)
    {
        parent::__construct($preserveKeys);

        $this->collections = $collections;
    }

    /**
     * @inheritDoc
     */
    protected function generator(\Traversable $iterator): \Generator
    {
        foreach ($iterator as $offset => $item) {
            yield $offset => $item;
        }

        foreach ($this->collections as $collection) {
            foreach ($collection as $offset => $item) {
                yield $offset => $item;
            }
        }
    }
}
