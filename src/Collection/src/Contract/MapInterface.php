<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Contract;

/**
 * Map interface.
 *
 * @template K of array-key
 * @template V
 * @extends \IteratorAggregate<K,V>
 */
interface MapInterface extends \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * @return CollectionInterface<V>
     */
    public function values(): CollectionInterface;

    /**
     * @return CollectionInterface<K>
     */
    public function keys(): CollectionInterface;

    /**
     * @return K|null
     */
    public function firstKey();

    /**
     * @return K|null
     */
    public function lastKey();

    /**
     * @param K $key
     * @param V $element
     */
    public function set($key, $element): void;

    /**
     * @param K $key
     */
    public function unset($key): void;
}
