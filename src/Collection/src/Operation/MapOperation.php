<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Operation;

/**
 * @template K of array-key
 * @template V
 * @template M
 * @extends AbstractOperation<K,V,K,M>
 */
final class MapOperation extends AbstractOperation
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable(V,K):M $callback
     * @param bool $preserveKeys
     */
    public function __construct(callable $callback, bool $preserveKeys = false)
    {
        parent::__construct($preserveKeys);

        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    protected function generator(\Traversable $iterator): \Generator
    {
        /**
         * @var K $offset
         * @var V $value
         */
        foreach ($iterator as $offset => $value) {
            yield $offset => ($this->callback)($value, $offset);
        }
    }
}
