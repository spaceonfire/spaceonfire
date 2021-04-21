<?php

declare(strict_types=1);

namespace spaceonfire\Type\Factory;

use spaceonfire\Type\Exception\TypeNotSupportedException;
use spaceonfire\Type\Type;
use spaceonfire\Type\VoidType;

final class VoidTypeFactory implements TypeFactoryInterface
{
    use TypeFactoryTrait;

    /**
     * @inheritDoc
     */
    public function supports(string $type): bool
    {
        $type = $this->removeWhitespaces($type);
        return VoidType::NAME === $type;
    }

    /**
     * @inheritDoc
     */
    public function make(string $type): Type
    {
        $type = $this->removeWhitespaces($type);

        if (!$this->supports($type)) {
            throw new TypeNotSupportedException($type, VoidType::class);
        }

        return new VoidType();
    }
}
