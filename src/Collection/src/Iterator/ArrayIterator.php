<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Iterator;

use spaceonfire\Collection\Contract\MutableInterface;

/**
 * @template K of array-key
 * @template V
 * @implements \IteratorAggregate<K,V>
 * @implements MutableInterface<V>
 */
final class ArrayIterator implements \IteratorAggregate, \Countable, MutableInterface
{
    /**
     * @var \ArrayIterator<K,V>
     */
    private \ArrayIterator $iterator;

    /**
     * @param iterable<K,V> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->iterator = $this->prepareIterator($items);
    }

    public function clear(): void
    {
        $this->iterator = new \ArrayIterator([], $this->iterator->getFlags());
    }

    /**
     * @param V ...$elements
     */
    public function add(...$elements): void
    {
        foreach ($elements as $element) {
            $this->iterator[] = $element;
        }
    }

    /**
     * @param V ...$elements
     */
    public function remove(...$elements): void
    {
        $this->iterator = new \ArrayIterator(
            array_filter(
                $this->iterator->getArrayCopy(),
                static fn ($element) => !in_array($element, $elements, true)
            ),
            $this->iterator->getFlags()
        );
    }

    /**
     * @param V $element
     * @param V $replacement
     */
    public function replace($element, $replacement): void
    {
        $array = $this->iterator->getArrayCopy();
        $index = array_search($element, $array, true);

        if (false === $index) {
            return;
        }

        $array[$index] = $replacement;

        $this->iterator = new \ArrayIterator($array, $this->iterator->getFlags());
    }

    /**
     * @return \Generator<K,V>
     */
    public function getIterator(): \Generator
    {
        yield from $this->iterator;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->iterator->count();
    }

    /**
     * @param iterable<K,V> $iterator
     * @return \ArrayIterator<int|K,V>
     */
    private function prepareIterator(iterable $iterator): \ArrayIterator
    {
        if ($iterator instanceof \ArrayIterator) {
            return $iterator;
        }

        return new \ArrayIterator($iterator instanceof \Traversable ? iterator_to_array($iterator) : $iterator);
    }
}
