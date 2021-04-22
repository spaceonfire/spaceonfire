<?php

declare(strict_types=1);

namespace spaceonfire\DataSource\Bridge\CycleOrm\Collection;

use RuntimeException;
use SplObjectStorage;

trait PivotAwareTrait
{
    /**
     * @var SplObjectStorage|null
     */
    protected ?SplObjectStorage $pivotContext = null;

    /**
     * Get associated pivot data.
     *
     * @return SplObjectStorage
     */
    public function getPivotContext(): SplObjectStorage
    {
        if (null === $this->pivotContext) {
            throw new RuntimeException('Pivot context not defined');
        }

        return $this->pivotContext;
    }

    /**
     * Set associated pivot data.
     *
     * @param SplObjectStorage $pivotContext
     */
    public function setPivotContext(SplObjectStorage $pivotContext): void
    {
        $this->pivotContext = $pivotContext;
    }
}
