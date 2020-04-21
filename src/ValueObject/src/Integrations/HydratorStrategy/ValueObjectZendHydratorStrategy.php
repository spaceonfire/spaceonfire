<?php

declare(strict_types=1);

namespace spaceonfire\ValueObject\Integrations\HydratorStrategy;

use spaceonfire\ValueObject\BaseValueObject;
use Webmozart\Assert\Assert;
use Zend\Hydrator\Strategy\StrategyInterface;

/**
 * Class ValueObjectZendHydratorStrategy
 * @package spaceonfire\ValueObject\Integrations\HydratorStrategy
 * @codeCoverageIgnore
 */
final class ValueObjectZendHydratorStrategy implements StrategyInterface
{
    /**
     * @var string|BaseValueObject
     */
    private $valueObjectClass;

    /**
     * ValueObjectZendHydratorStrategy constructor.
     * @param string|BaseValueObject $valueObjectClass
     */
    public function __construct(string $valueObjectClass)
    {
        Assert::subclassOf($valueObjectClass, BaseValueObject::class);
        $this->valueObjectClass = $valueObjectClass;
    }

    /**
     * @inheritDoc
     * @param BaseValueObject $value
     */
    public function extract($value, ?object $object = null)
    {
        Assert::isInstanceOf($value, $this->valueObjectClass);
        return $value->value();
    }

    /**
     * @inheritDoc
     */
    public function hydrate($value, ?array $data)
    {
        return new $this->valueObjectClass($value);
    }
}