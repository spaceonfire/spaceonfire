<?php

declare(strict_types=1);

namespace spaceonfire\Bridge\Cycle\Repository;

use Cycle\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\TransactionInterface;
use spaceonfire\Bridge\Cycle\CycleEntityManager;
use spaceonfire\Collection\CollectionInterface;
use spaceonfire\Criteria\CriteriaInterface;
use spaceonfire\DataSource\RepositoryInterface;

/**
 * @template E of object
 * @template P
 * @implements RepositoryInterface<E,P>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var string|class-string<E>
     * @phpstan-var class-string<E>
     */
    protected string $role;

    protected ORMInterface $orm;

    /**
     * @var CycleEntityManager<E>
     */
    protected CycleEntityManager $em;

    public function __construct(
        string $role,
        ORMInterface $orm,
        int $transactionMode = TransactionInterface::MODE_CASCADE
    ) {
        /** @phpstan-var class-string<E> $role */
        $role = $orm->resolveRole($role);
        $this->orm = $orm;
        $this->role = $role;
        $this->em = new CycleEntityManager($this->orm, $transactionMode);
    }

    public function findByPrimary($primary, ?CriteriaInterface $criteria = null): object
    {
        return $this->em->getReader($this->role)->findByPrimary($primary, $criteria);
    }

    public function findAll(?CriteriaInterface $criteria = null): CollectionInterface
    {
        return $this->em->getReader($this->role)->findAll($criteria);
    }

    public function findOne(?CriteriaInterface $criteria = null): ?object
    {
        return $this->em->getReader($this->role)->findOne($criteria);
    }

    public function count(?CriteriaInterface $criteria = null): int
    {
        return $this->em->getReader($this->role)->count($criteria);
    }

    public function save(object $entity, object ...$entities): void
    {
        $this->em->getPersister($this->role)->save($entity, ...$entities);
    }

    public function remove(object $entity, object ...$entities): void
    {
        $this->em->getPersister($this->role)->remove($entity, ...$entities);
    }

    public function getMapper(): ORM\MapperInterface
    {
        return $this->orm->getMapper($this->role);
    }
}
