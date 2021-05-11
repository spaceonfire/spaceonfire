<?php

declare(strict_types=1);

namespace spaceonfire\Collection;

use spaceonfire\Collection\Contract\MutableInterface;
use spaceonfire\Collection\Iterator\ArrayCacheIterator;
use spaceonfire\Collection\Iterator\ArrayIterator;
use spaceonfire\Type\MixedType;
use spaceonfire\Type\TypeInterface;

/**
 * @template V
 * @extends AbstractCollection<V>
 * @implements MutableInterface<V>
 */
final class Collection extends AbstractCollection implements MutableInterface
{
    private TypeInterface $valueType;

    /**
     * @param \Traversable<int,V> $source
     * @param TypeInterface $valueType
     */
    private function __construct(\Traversable $source, TypeInterface $valueType)
    {
        $this->valueType = $valueType;
        $this->source = $source;
    }

    /**
     * @param array<V>|\Traversable<V> $elements
     * @return static<V>
     */
    public static function new(iterable $elements = [], ?TypeInterface $valueType = null): self
    {
        if ($elements instanceof self) {
            return $elements;
        }

        $valueType ??= new MixedType();

        $iterator = \is_array($elements) ? new \ArrayIterator($elements) : $elements;
        $iterator = (new Operation\ValuesOperation())->apply($iterator);
        $iterator = (new Operation\TypeCheckOperation($valueType))->apply($iterator);

        return new self(ArrayCacheIterator::wrap($iterator), $valueType);
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->source = $this->getMutableSource();
        $this->source->clear();
    }

    /**
     * @inheritDoc
     */
    public function add(...$elements): void
    {
        $this->assertElementsType(...$elements);
        $this->source = $this->getMutableSource();
        $this->source->add(...$elements);
    }

    /**
     * @inheritDoc
     */
    public function remove(...$elements): void
    {
        $this->assertElementsType(...$elements);
        $this->source = $this->getMutableSource();
        $this->source->remove(...$elements);
    }

    /**
     * @inheritDoc
     */
    public function replace($element, $replacement): void
    {
        $this->assertElementsType($element, $replacement);
        $this->source = $this->getMutableSource();
        $this->source->replace($element, $replacement);
    }

    /**
     * @inheritDoc
     */
    protected function withSource(\Traversable $source): AbstractCollection
    {
        return new self($source, $this->valueType);
    }

    /**
     * @phpstan-return MutableInterface<V>&\Traversable<int,V>
     */
    private function getMutableSource(): MutableInterface
    {
        return $this->source instanceof MutableInterface
            ? $this->source
            : new ArrayIterator(\iterator_to_array($this->source, false));
    }

    /**
     * @param V ...$elements
     */
    private function assertElementsType(...$elements): void
    {
        foreach ($elements as $element) {
            if ($this->valueType->check($element)) {
                continue;
            }

            throw new \LogicException(sprintf(
                'Collection accepts only elements of type %s. Got: %s',
                $this->valueType,
                get_debug_type($element)
            ));
        }
    }
}
