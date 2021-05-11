<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Iterator;

/**
 * @template K of array-key
 * @template V
 * @implements \IteratorAggregate<K,V>
 */
final class ArrayCacheIterator implements \IteratorAggregate, \Countable
{
    /**
     * @var \Traversable<K,V>|null
     */
    private ?\Traversable $iterator;

    /**
     * @var array<K,V>|null
     */
    private ?array $array = null;

    /**
     * @param \Traversable<K,V> $iterator
     */
    private function __construct(\Traversable $iterator)
    {
        $this->iterator = $iterator;
    }

    public function __destruct()
    {
        $this->iterator = null;
        $this->array = null;
    }

    /**
     * @param \Traversable<K,V> $iterator
     * @return self<K,V>
     */
    public static function wrap(\Traversable $iterator): self
    {
        if ($iterator instanceof self) {
            return $iterator;
        }

        return new self($iterator);
    }

    /**
     * @return \Generator<K,V>
     */
    public function getIterator(): \Generator
    {
        if (null !== $this->array) {
            return yield from $this->array;
        }

        \assert(null !== $this->iterator);

        $this->array = [];

        foreach ($this->iterator as $offset => $value) {
            yield $offset => $value;
            $this->array[$offset] = $value;
        }

        $this->iterator = null;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        if (null !== $this->array) {
            return \count($this->array);
        }

        return \iterator_count($this->getIterator());
    }
}
