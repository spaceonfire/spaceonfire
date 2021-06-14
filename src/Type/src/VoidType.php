<?php

declare(strict_types=1);

namespace spaceonfire\Type;

use RuntimeException;

final class VoidType implements TypeInterface
{
    public const NAME = 'void';

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return self::NAME;
    }

    /**
     * @inheritDoc
     */
    public function check($value): bool
    {
        throw new RuntimeException('Void type cannot be checked');
    }
}
