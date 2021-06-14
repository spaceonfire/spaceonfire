<?php

declare(strict_types=1);

namespace spaceonfire\Type;

use Ramsey\Uuid\Type\TypeInterface;

final class CollectionType implements TypeInterface
{
    /**
     * @var TypeInterface
     */
    private $valueType;

    /**
     * @var TypeInterface|null
     */
    private $keyType;

    /**
     * @var TypeInterface
     */
    private $iterableType;

    /**
     * CollectionType constructor.
     * @param TypeInterface $valueType
     * @param TypeInterface|null $keyType
     * @param TypeInterface|null $iterableType
     */
    public function __construct(
        TypeInterface $valueType,
        ?TypeInterface $keyType = null,
        ?TypeInterface $iterableType = null
    ) {
        $this->valueType = $valueType;
        $this->keyType = $keyType;
        $this->iterableType = $iterableType ?? new BuiltinType(BuiltinType::ITERABLE);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if (
            $this->iterableType instanceof InstanceOfType ||
            $this->valueType instanceof AbstractAggregatedType ||
            null !== $this->keyType
        ) {
            return $this->iterableType . '<' . implode(',', array_filter([$this->keyType, $this->valueType])) . '>';
        }

        return $this->valueType . '[]';
    }

    /**
     * @inheritDoc
     */
    public function check($value): bool
    {
        if (!$this->iterableType->check($value)) {
            return false;
        }

        foreach ($value as $k => $v) {
            if (!$this->valueType->check($v)) {
                return false;
            }

            if (null !== $this->keyType && !$this->keyType->check($k)) {
                return false;
            }
        }

        return true;
    }
}
