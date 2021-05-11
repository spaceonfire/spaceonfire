<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Operation;

use spaceonfire\Collection\Contract\OperationInterface;
use spaceonfire\Common\Data\Field\YiiArrayField;
use spaceonfire\Criteria\CriteriaInterface;

/**
 * @template K of array-key
 * @template V
 * @implements OperationInterface<K,V,K,V>
 */
final class MatchingOperation implements OperationInterface
{
    private CriteriaInterface $criteria;

    private bool $preserveKeys;

    public function __construct(CriteriaInterface $criteria, bool $preserveKeys = false)
    {
        $this->criteria = $criteria;
        $this->preserveKeys = $preserveKeys;
    }

    /**
     * @inheritDoc
     */
    public function apply(\Traversable $iterator): \Iterator
    {
        if (null !== $where = $this->criteria->getWhere()) {
            $iterator = (new FilterOperation(static fn ($value) => $where->evaluate($value), $this->preserveKeys))
                ->apply($iterator);
        }

        if ([] !== $orderBy = $this->criteria->getOrderBy()) {
            foreach ($orderBy as $key => $direction) {
                // TODO: wrap $key to field with factory
                $iterator = (new SortOperation($direction, new YiiArrayField($key), $this->preserveKeys))
                    ->apply($iterator);
            }
        }

        return (new SliceOperation($this->criteria->getOffset(), $this->criteria->getLimit(), $this->preserveKeys))
            ->apply($iterator);
    }
}
