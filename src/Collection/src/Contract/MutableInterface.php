<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Contract;

/**
 * @template V
 */
interface MutableInterface
{
    /**
     * Clear collection.
     */
    public function clear(): void;

    /**
     * Add element(s) to collection.
     * @param V ...$elements
     */
    public function add(...$elements): void;

    /**
     * Remove element(s) from collection.
     * @param V ...$elements
     */
    public function remove(...$elements): void;

    /**
     * Replace one element with another.
     * @param V $element
     * @param V $replacement
     */
    public function replace($element, $replacement): void;
}
