<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Operation;

/**
 * @template K of array-key
 * @template V
 * @template R
 * @extends AbstractOperation<K,V,int,R|null>
 */
final class ReduceOperation extends AbstractOperation
{
    /**
     * @var callable(R|null,V):R
     */
    private $callback;

    /**
     * @var R|null
     */
    private $initialValue;

    /**
     * @param callable(R|null,V):R $callback
     * @param R|null $initialValue
     * @param bool $preserveKeys
     */
    public function __construct(callable $callback, $initialValue = null, bool $preserveKeys = false)
    {
        parent::__construct($preserveKeys);

        $this->callback = $callback;
        $this->initialValue = $initialValue;
    }

    /**
     * @inheritDoc
     */
    protected function generator(\Traversable $iterator): \Generator
    {
        $output = $this->initialValue;

        /** @var V $value */
        foreach ($iterator as $value) {
            $output = ($this->callback)($output, $value);
        }

        return yield $output;
    }
}
