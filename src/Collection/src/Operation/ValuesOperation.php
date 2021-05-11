<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Operation;

use spaceonfire\Collection\Contract\OperationInterface;

/**
 * @template K of array-key
 * @template V
 * @implements OperationInterface<K,V,int,V>
 */
final class ValuesOperation implements OperationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(\Traversable $iterator): \Iterator
    {
        foreach ($iterator as $value) {
            yield $value;
        }
    }
}
