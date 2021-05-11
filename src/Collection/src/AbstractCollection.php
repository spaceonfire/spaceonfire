<?php

declare(strict_types=1);

namespace spaceonfire\Collection;

use spaceonfire\Collection\Contract\CollectionInterface;
use spaceonfire\Collection\Contract\OperationInterface;
use spaceonfire\Collection\Iterator\ArrayCacheIterator;
use spaceonfire\Common\Data\FieldInterface;
use spaceonfire\Criteria\CriteriaInterface;
use spaceonfire\Criteria\FilterableInterface;

/**
 * @template V
 * @implements CollectionInterface<V>
 */
abstract class AbstractCollection implements CollectionInterface, FilterableInterface
{
    /**
     * @var \Traversable<int,V>
     */
    protected \Traversable $source;

    public function __clone()
    {
        $this->source = new \ArrayIterator($this->all());
    }

    public function applyOperation(OperationInterface $operation): CollectionInterface
    {
        return $this->withSource($operation->apply($this->source));
    }

    public function filter(?callable $callback = null): CollectionInterface
    {
        return $this->applyOperation(new Operation\FilterOperation($callback));
    }

    public function map(callable $callback): CollectionInterface
    {
        return $this->applyOperation(new Operation\MapOperation($callback));
    }

    public function reverse(): CollectionInterface
    {
        return $this->applyOperation(new Operation\ReverseOperation());
    }

    public function merge(iterable ...$collections): CollectionInterface
    {
        return $this->applyOperation(new Operation\MergeOperation(false, ...$collections));
    }

    public function unique(): CollectionInterface
    {
        return $this->applyOperation(new Operation\UniqueOperation());
    }

    public function sort(?FieldInterface $field = null, int $direction = SORT_ASC): CollectionInterface
    {
        return $this->applyOperation(new Operation\SortOperation($direction, $field));
    }

    public function slice(int $offset, ?int $limit = null): CollectionInterface
    {
        return $this->applyOperation(new Operation\SliceOperation($offset, $limit));
    }

    /**
     * @param CriteriaInterface $criteria
     * @return CollectionInterface<V>
     */
    public function matching(CriteriaInterface $criteria): CollectionInterface
    {
        return $this->applyOperation(new Operation\MatchingOperation($criteria));
    }

    public function all(): array
    {
        return \iterator_to_array($this->getIterator(), false);
    }

    public function find(callable $callback)
    {
        $iter = (new Operation\FirstOperation($callback))->apply($this->source);

        return $iter->valid()
            ? $iter->current()
            : null;
    }

    public function contains($element): bool
    {
        return null !== $this->find(static fn ($v) => $element === $v);
    }

    public function first()
    {
        $iter = (new Operation\FirstOperation())->apply($this->source);

        return $iter->valid()
            ? $iter->current()
            : null;
    }

    public function last()
    {
        $isEmpty = true;

        foreach ($this->source as $value) {
            $isEmpty = false;
        }

        return $isEmpty ? null : $value ?? null;
    }

    public function reduce(callable $callback, $initialValue = null)
    {
        $iter = (new Operation\ReduceOperation($callback, $initialValue))->apply($this->source);

        return $iter->valid()
            ? $iter->current()
            : $initialValue;
    }

    public function implode(?string $glue = null, ?FieldInterface $field = null): string
    {
        $i = -1;

        $output = $this->reduce(static function ($accum, $element) use ($glue, $field, &$i) {
            $value = null !== $field ? $field->extract($element) : $element;

            if (0 === ++$i) {
                return '' . $value;
            }

            return $accum . $glue . $value;
        }, '');

        \assert(\is_string($output));

        return $output;
    }

    public function sum(?FieldInterface $field = null)
    {
        $output = $this->reduce(static function ($accum, $element) use ($field) {
            $value = null !== $field ? $field->extract($element) : $element;

            if (!\is_numeric($value)) {
                throw new \LogicException('Non-numeric value used in ' . __METHOD__);
            }

            return $accum + $value;
        }, 0);

        \assert(null !== $output);

        return $output;
    }

    public function average(?FieldInterface $field = null)
    {
        if (0 === $count = $this->count()) {
            return null;
        }

        return $this->sum($field) / $count;
    }

    public function median(?FieldInterface $field = null)
    {
        $array = [];

        foreach ($this->source as $element) {
            $value = null === $field ? $element : $field->extract($element);

            if (!\is_numeric($value)) {
                throw new \LogicException('Non-numeric value used in ' . __METHOD__);
            }

            $array[] = false === \filter_var($value, \FILTER_VALIDATE_INT) ? (float)$value : (int)$value;
        }

        if ([] === $array) {
            return null;
        }

        \usort($array, static fn ($left, $right) => $left <=> $right);

        $count = \count($array);
        $middleIndex = (int)\floor(($count - 1) / 2);

        if ($count % 2) {
            return $array[$middleIndex];
        }

        return ($array[$middleIndex] + $array[$middleIndex + 1]) / 2;
    }

    public function max(?FieldInterface $field = null)
    {
        return $this->reduce(static function ($accum, $item) use ($field) {
            $value = null === $field ? $item : $field->extract($item);
            $value ??= 0;

            if (!\is_numeric($value)) {
                throw new \LogicException('Non-numeric value used in ' . __METHOD__);
            }

            if (null === $accum) {
                return $value;
            }

            return $value > $accum ? $value : $accum;
        });
    }

    public function min(?FieldInterface $field = null)
    {
        return $this->reduce(static function ($accum, $item) use ($field) {
            $value = null === $field ? $item : $field->extract($item);
            $value ??= 0;

            if (!\is_numeric($value)) {
                throw new \LogicException('Non-numeric value used in ' . __METHOD__);
            }

            if (null === $accum) {
                return $value;
            }

            return $value < $accum ? $value : $accum;
        });
    }

    /**
     * @return \Traversable<int,V>
     */
    public function getIterator(): \Traversable
    {
        if (!$this->source instanceof \IteratorAggregate) {
            $this->source = ArrayCacheIterator::wrap($this->source);
        }

        return $this->source;
    }

    public function count(): int
    {
        if (!$this->source instanceof \Countable) {
            $this->source = ArrayCacheIterator::wrap($this->source);
        }

        return $this->source->count();
    }

    /**
     * @inheritDoc
     * @phpstan-return list<V>
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }

    /**
     * @template T
     * @param \Traversable<int,T> $source
     * @return static<T>
     */
    abstract protected function withSource(\Traversable $source): CollectionInterface;
}
