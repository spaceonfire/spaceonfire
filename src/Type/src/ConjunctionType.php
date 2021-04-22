<?php

declare(strict_types=1);

namespace spaceonfire\Type;

final class ConjunctionType extends AbstractAggregatedType
{
    public const DELIMITER = '&';

    /**
     * ConjunctionType constructor.
     * @param TypeInterface[] $conjuncts
     */
    public function __construct(iterable $conjuncts)
    {
        parent::__construct($conjuncts, self::DELIMITER);
    }

    /**
     * @inheritDoc
     */
    public function check($value): bool
    {
        foreach ($this->types as $type) {
            if (!$type->check($value)) {
                return false;
            }
        }

        return true;
    }
}
