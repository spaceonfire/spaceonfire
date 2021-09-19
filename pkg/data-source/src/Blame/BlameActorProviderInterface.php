<?php

declare(strict_types=1);

namespace spaceonfire\DataSource\Blame;

/**
 * @template T of object
 */
interface BlameActorProviderInterface
{
    /**
     * @return T|null
     */
    public function getActor(): ?object;
}
