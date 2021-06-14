<?php

declare(strict_types=1);

namespace spaceonfire\Container\Fixtures\AbstractClass;

final class RequiresAbstractClass
{
    private \spaceonfire\Container\Fixtures\AbstractClass\AbstractClass $abstractClass;

    public function __construct(AbstractClass $abstractClass)
    {
        $this->abstractClass = $abstractClass;
    }

    /**
     * Getter for `abstractClass` property.
     * @return AbstractClass
     */
    public function getAbstractClass(): AbstractClass
    {
        return $this->abstractClass;
    }
}
