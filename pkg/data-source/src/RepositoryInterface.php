<?php

declare(strict_types=1);

namespace spaceonfire\DataSource;

/**
 * @template E of object
 * @template P
 * @extends EntityReaderInterface<E,P>
 * @extends EntityPersisterInterface<E>
 */
interface RepositoryInterface extends EntityReaderInterface, EntityPersisterInterface
{
}
