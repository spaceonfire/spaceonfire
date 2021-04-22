<?php

declare(strict_types=1);

namespace spaceonfire\Type;

final class DisjunctionType extends AbstractAggregatedType
{
    public const DELIMITER = '|';

    /**
     * DisjunctionType constructor.
     * @param TypeInterface[] $disjuncts
     */
    public function __construct(iterable $disjuncts)
    {
        parent::__construct($disjuncts, self::DELIMITER);
    }

    /**
     * @inheritDoc
     */
    public function check($value): bool
    {
        foreach ($this->types as $type) {
            if ($type->check($value)) {
                return true;
            }
        }

        return false;
    }
}
