<?php

declare(strict_types=1);

namespace spaceonfire\DataSource\Bridge\CycleOrm\Repository;

use Cycle\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Transaction;
use spaceonfire\Collection\CollectionInterface;
use spaceonfire\Criteria\CriteriaInterface;
use spaceonfire\DataSource\Bridge\CycleOrm\Query\CycleQuery;
use spaceonfire\DataSource\EntityInterface;
use spaceonfire\DataSource\Exceptions\NotFoundException;
use spaceonfire\DataSource\Exceptions\RemoveException;
use spaceonfire\DataSource\Exceptions\SaveException;
use spaceonfire\DataSource\MapperInterface;
use spaceonfire\DataSource\QueryInterface;
use spaceonfire\DataSource\RepositoryInterface;
use Webmozart\Assert\Assert;

abstract class AbstractCycleRepositoryAdapter implements RepositoryInterface
{
    protected string $role;

    protected ORM\RepositoryInterface $repository;

    protected ORMInterface $orm;

    protected Transaction $transaction;

    /**
     * @param string $role
     * @param ORMInterface $orm
     */
    public function __construct(string $role, ORMInterface $orm)
    {
        $this->role = $role;
        $this->orm = $orm;
        $this->repository = $orm->getRepository($role);
        $this->transaction = new Transaction($orm);
    }

    /**
     * @inheritDoc
     * @param bool $cascade
     */
    public function save($entity, bool $cascade = true): void
    {
        $this->assertEntity($entity);

        $this->transaction->persist(
            $entity,
            $cascade ? Transaction::MODE_CASCADE : Transaction::MODE_ENTITY_ONLY
        );

        try {
            $this->transaction->run();
        } catch (\Throwable $e) {
            throw static::makeSaveException($e);
        }
    }

    /**
     * @inheritDoc
     * @param bool $cascade
     */
    public function remove($entity, bool $cascade = true): void
    {
        $this->assertEntity($entity);

        $this->transaction->delete(
            $entity,
            $cascade ? Transaction::MODE_CASCADE : Transaction::MODE_ENTITY_ONLY
        );

        try {
            $this->transaction->run();
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            throw static::makeRemoveException($e);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @inheritDoc
     */
    public function findByPrimary($primary)
    {
        $entity = $this->repository->findByPK($primary);

        if (null === $entity) {
            throw static::makeNotFoundException($primary);
        }

        $this->assertEntity($entity);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function findAll(?CriteriaInterface $criteria = null): CollectionInterface
    {
        $query = $this->query();

        if (null !== $criteria) {
            $query->matching($criteria);
        }

        return $query->fetchAll();
    }

    /**
     * @inheritDoc
     */
    public function findOne(?CriteriaInterface $criteria = null)
    {
        $query = $this->query();

        if (null !== $criteria) {
            $query->matching($criteria);
        }

        $entity = $query->fetchOne();

        if (null === $entity) {
            return null;
        }

        $this->assertEntity($entity);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function count(?CriteriaInterface $criteria = null): int
    {
        $query = $this->query();

        if (null !== $criteria) {
            $query->matching($criteria);
        }

        return $query->count();
    }

    /**
     * @inheritDoc
     */
    public function getMapper(): MapperInterface
    {
        /** @var MapperInterface $mapper */
        $mapper = $this->orm->getMapper($this->role);
        Assert::isInstanceOf($mapper, MapperInterface::class);
        return $mapper;
    }

    protected function assertEntity(object $entity): void
    {
        $entityClass = $this->orm->getSchema()->define($this->role, ORM\Schema::ENTITY);

        if (null === $entityClass || ($entityClass === $this->role && !class_exists($entityClass))) {
            return;
        }

        $entityClasses = [
            EntityInterface::class,
            $entityClass,
        ];

        foreach ($entityClasses as $class) {
            Assert::isInstanceOf($entity, $class, 'Associated with repository class must implement %2$s. Got: %s');
        }
    }

    /**
     * @param mixed|null $primary
     * @return NotFoundException
     * @codeCoverageIgnore
     */
    protected static function makeNotFoundException($primary = null): NotFoundException
    {
        return new NotFoundException(null, compact('primary'));
    }

    /**
     * @param \Throwable $e
     * @return RemoveException
     * @codeCoverageIgnore
     */
    protected static function makeRemoveException(\Throwable $e): RemoveException
    {
        return new RemoveException(null, [], 0, $e);
    }

    /**
     * @param \Throwable $e
     * @return SaveException
     * @codeCoverageIgnore
     */
    protected static function makeSaveException(\Throwable $e): SaveException
    {
        return new SaveException(null, [], 0, $e);
    }

    /**
     * Creates query
     */
    protected function query(): QueryInterface
    {
        return new CycleQuery($this->repository->select(), $this->getMapper());
    }
}
