<?php

declare(strict_types=1);

namespace spaceonfire\Collection;

use RuntimeException;
use stdClass;

/**
 * Class `TypedCollection` allows you to create collection witch items are the same type.
 *
 * For testing scalar types provide type name from
 * [return values of `gettype` function](https://www.php.net/manual/en/function.gettype.php):
 *
 * ```php
 * $integers = new TypedCollection($items, 'integer');
 * $strings = new TypedCollection($items, 'string');
 * $floats = new TypedCollection($items, 'double');
 * ```
 *
 * If you want to test values are objects than provide class or interface name:
 *
 * ```php
 * $dateTime = new TypedCollection($items, \DateTime::class);
 * $jsonSerializable = new TypedCollection($items, \JsonSerializable::class);
 * ```
 *
 * @package spaceonfire\Collection
 */
class TypedCollection extends BaseCollection
{
    /**
     * @var string
     */
    protected $type;

    /**
     * TypedCollection constructor.
     * @param array $items
     * @param string $type Scalar type name or Full qualified name of object class
     */
    public function __construct($items = [], string $type = stdClass::class)
    {
        $this->type = $type;
        parent::__construct($items);
    }

    /**
     * Check that item are the same type as collection requires
     * @param mixed $item
     * @return bool
     */
    protected function checkType($item): bool
    {
        $type = gettype($item);

        if ($type === 'object' && !class_exists($this->type) && !interface_exists($this->type)) {
            throw new RuntimeException('Class ' . $this->type . ' does not exist');
        }

        if (($type === 'object' && !($item instanceof $this->type)) ||
            ($type !== 'object' && $type !== $this->type)) {
            throw new RuntimeException(static::class . ' accept only instances of ' . $this->type);
        }

        return true;
    }

    /** {@inheritDoc} */
    protected function getItems($items): array
    {
        $result = parent::getItems($items);
        foreach ($result as $item) {
            $this->checkType($item);
        }
        return $result;
    }

    /** {@inheritDoc} */
    protected function newStatic(array $items = []): CollectionInterface
    {
        if (static::class === __CLASS__) {
            return new static($items, $this->type);
        }

        return parent::newStatic($items);
    }

    /** {@inheritDoc} */
    public function offsetSet($offset, $value)
    {
        $this->checkType($value);
        parent::offsetSet($offset, $value);
    }

    /**
     * Converts current collection to lower level collection without type check
     * @return CollectionInterface
     */
    public function downgrade(): CollectionInterface
    {
        return new Collection($this->all());
    }

    /** {@inheritDoc} */
    public function keys()
    {
        return $this->downgrade()->keys();
    }

    /** {@inheritDoc} */
    public function flip()
    {
        return $this->downgrade()->flip();
    }

    /**
     * {@inheritDoc}
     * Also collection will be downgraded
     */
    public function remap($from, $to)
    {
        return $this->downgrade()->remap($from, $to);
    }

    /** {@inheritDoc} */
    public function indexBy($key)
    {
        return $this->newStatic(parent::indexBy($key)->all());
    }

    /** {@inheritDoc} */
    public function groupBy($groupField, $preserveKeys = true)
    {
        return $this->downgrade()
            ->groupBy($groupField, $preserveKeys)
            ->map(function (CollectionInterface $group) {
                return $this->newStatic($group->all());
            });
    }

    /**
     * {@inheritDoc}
     * Also collection will be downgraded
     */
    public function map(callable $callback)
    {
        return $this->downgrade()->map($callback);
    }

    /** {@inheritDoc} */
    public function replace($item, $replacement, $strict = true)
    {
        $this->checkType($item);
        $this->checkType($replacement);
        return $this->newStatic(parent::replace($item, $replacement, $strict)->all());
    }
}
