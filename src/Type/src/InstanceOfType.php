<?php

declare(strict_types=1);

namespace spaceonfire\Type;

use InvalidArgumentException;

final class InstanceOfType implements TypeInterface
{
    /**
     * @var string
     */
    private string $className;

    /**
     * InstanceOfType constructor.
     * @param string $className
     */
    public function __construct(string $className)
    {
        if (!class_exists($className) && !interface_exists($className)) {
            throw new InvalidArgumentException(sprintf('Type "%s" is not supported by %s', $className, __CLASS__));
        }

        $this->className = $className;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->className;
    }

    /**
     * @inheritDoc
     */
    public function check($value): bool
    {
        return $value instanceof $this->className;
    }
}
