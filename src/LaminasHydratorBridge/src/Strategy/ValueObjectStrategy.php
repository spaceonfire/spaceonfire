<?php

declare(strict_types=1);

namespace spaceonfire\LaminasHydratorBridge\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use spaceonfire\ValueObject\BaseValueObject;
use Webmozart\Assert\Assert;

/**
 * Class ValueObjectStrategy
 *
 * Attention: You should not extend this class because it will become final in the next major release
 * after the backward compatibility aliases are removed.
 *
 * @final
 */
final class ValueObjectStrategy implements StrategyInterface
{
    private string $valueObjectClass;

    /**
     * ValueObjectStrategy constructor.
     * @param string|BaseValueObject $valueObjectClass
     */
    public function __construct(string $valueObjectClass)
    {
        Assert::subclassOf($valueObjectClass, BaseValueObject::class);
        $this->valueObjectClass = $valueObjectClass;
    }

    /**
     * @inheritDoc
     * @param BaseValueObject|mixed $value
     */
    public function extract($value, ?object $object = null)
    {
        $class = $this->valueObjectClass;

        if ($value instanceof $class) {
            return $value->value();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function hydrate($value, ?array $data = null)
    {
        $class = $this->valueObjectClass;

        if ($value instanceof $class) {
            return $value;
        }

        return new $class($value);
    }
}
