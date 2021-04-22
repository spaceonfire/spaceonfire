<?php

declare(strict_types=1);

namespace spaceonfire\Container\Fixtures\AbstractClass;

final class AcceptNullableAbstractClass
{
    private ?\spaceonfire\Container\Fixtures\AbstractClass\AbstractClass $abstractClass = null;

    public function __construct(?AbstractClass $abstractClass)
    {
        $this->abstractClass = $abstractClass;
    }

    /**
     * Getter for `abstractClass` property.
     * @return AbstractClass|null
     */
    public function getAbstractClass(): ?AbstractClass
    {
        return $this->abstractClass;
    }
}
