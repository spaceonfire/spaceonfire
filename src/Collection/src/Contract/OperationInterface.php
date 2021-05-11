<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Contract;

/**
 * Operation interface.
 *
 * @template IK of array-key
 * @template IV
 * @template OK of array-key
 * @template OV
 */
interface OperationInterface
{
    /**
     * @param \Traversable<IK,IV> $iterator
     * @return \Iterator<OK,OV>
     */
    public function apply(\Traversable $iterator): \Iterator;
}
