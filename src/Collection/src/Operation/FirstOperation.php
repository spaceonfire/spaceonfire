<?php

declare(strict_types=1);

namespace spaceonfire\Collection\Operation;

/**
 * @template K of array-key
 * @template V
 * @extends AbstractOperation<K,V,K,V>
 */
final class FirstOperation extends AbstractOperation
{
    /**
     * @var callable(V,K,\Traversable<K,V>):bool|null
     */
    private $callback;

    /**
     * @param callable(V,K,\Traversable<K,V>):bool|null $callback
     * @param bool $preserveKeys
     */
    public function __construct(?callable $callback = null, bool $preserveKeys = false)
    {
        parent::__construct($preserveKeys);

        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    protected function generator(\Traversable $iterator): \Generator
    {
        $callback = $this->callback ?? static fn ($v) => true;

        foreach ($iterator as $offset => $value) {
            if (($callback)($value, $offset, $iterator)) {
                return yield $offset => $value;
            }
        }
    }
}
