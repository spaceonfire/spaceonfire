<?php

declare(strict_types=1);

namespace spaceonfire\Container\ServiceProvider;

use spaceonfire\Container\ContainerAwareTrait;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    use ContainerAwareTrait;

    protected ?string $identifier = null;

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $id): ServiceProviderInterface
    {
        $this->identifier = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier ?? static::class;
    }
}
