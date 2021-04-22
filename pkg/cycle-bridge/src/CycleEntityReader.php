<?php

declare(strict_types=1);

namespace spaceonfire\Bridge\Cycle;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Reference;
use Cycle\ORM\Promise\ReferenceInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use spaceonfire\Bridge\Cycle\Select\CriteriaScope;
use spaceonfire\Bridge\Cycle\Select\LazySelectIterator;
use spaceonfire\Bridge\Cycle\Select\ScopeAggregate;
use spaceonfire\Collection\Collection;
use spaceonfire\Collection\CollectionInterface;
use spaceonfire\Collection\Iterator\ArrayCacheIterator;
use spaceonfire\Criteria\Criteria;
use spaceonfire\Criteria\CriteriaInterface;
use spaceonfire\Criteria\Expression\ExpressionFactory;
use spaceonfire\DataSource\DefaultEntityNotFoundExceptionFactory;
use spaceonfire\DataSource\EntityNotFoundExceptionFactoryInterface;
use spaceonfire\DataSource\EntityReaderInterface;
use spaceonfire\Type\InstanceOfType;
use spaceonfire\Type\TypeInterface;

/**
 * @template E of object
 * @template P
 * @implements EntityReaderInterface<E,P>
 */
final class CycleEntityReader implements EntityReaderInterface
{
    private ORMInterface $orm;

    private string $role;

    /**
     * @var class-string<E>|null
     */
    private ?string $classname;

    private EntityNotFoundExceptionFactoryInterface $notFoundExceptionFactory;

    /**
     * @param ORMInterface $orm
     * @param string|class-string<E> $role
     * @phpstan-param class-string<E> $role
     * @param EntityNotFoundExceptionFactoryInterface|null $notFoundExceptionFactory
     */
    public function __construct(
        ORMInterface $orm,
        string $role,
        ?EntityNotFoundExceptionFactoryInterface $notFoundExceptionFactory = null
    ) {
        $this->orm = $orm;
        $this->role = $this->orm->resolveRole($role);
        $this->classname = $this->orm->getSchema()->define($this->role, SchemaInterface::ENTITY);
        $this->notFoundExceptionFactory = $notFoundExceptionFactory ?? new DefaultEntityNotFoundExceptionFactory();
    }

    public function findByPrimary($primary, ?CriteriaInterface $criteria = null): object
    {
        /** @phpstan-var E|null $entity */
        $entity = $this->findReferenceInHeap($this->getPrimaryReference($primary))
            ?? $this->findOne($this->getPrimaryCriteria($primary, $criteria));

        if (null === $entity) {
            throw $this->notFoundExceptionFactory->make($this->classname ?? $this->role, $primary);
        }

        return $entity;
    }

    public function findAll(?CriteriaInterface $criteria = null): CollectionInterface
    {
        return Collection::new(
            ArrayCacheIterator::wrap(new LazySelectIterator($this->makeSelect($criteria))),
            $this->getEntityType()
        );
    }

    public function findOne(?CriteriaInterface $criteria = null): ?object
    {
        $criteria = ($criteria ?? Criteria::new())->limit(1)->offset(0);

        /** @phpstan-var E|null $entity */
        $entity = $this->makeSelect($criteria)->fetchOne();

        return $entity ?? null;
    }

    public function count(?CriteriaInterface $criteria = null): int
    {
        if (null !== $criteria) {
            $criteria = $criteria->limit(null)->offset(null);
        }

        return $this->makeSelect($criteria)->count();
    }

    /**
     * @param CriteriaInterface|null $criteria
     * @return Select<E>
     */
    private function makeSelect(?CriteriaInterface $criteria = null): Select
    {
        $select = new Select($this->orm, $this->role);

        $scope = new ScopeAggregate();

        if (null !== $sourceScope = $this->orm->getSource($this->role)->getConstrain()) {
            $scope->add($sourceScope);
        }

        if (null !== $criteria) {
            $scope->add(new CriteriaScope($criteria, $this->orm, $this->role));

            // TODO: if only this can be done in CriteriaScope through QueryBuilder.
            $select->load($criteria->getInclude());
        }

        $select->scope($scope);

        return $select;
    }

    private function getEntityType(): ?TypeInterface
    {
        return null === $this->classname ? null : InstanceOfType::new($this->classname);
    }

    /**
     * @param P $primary
     * @return ReferenceInterface
     */
    private function getPrimaryReference($primary): ReferenceInterface
    {
        if ($primary instanceof ReferenceInterface) {
            \assert($primary->__role() === $this->role);
            return $primary;
        }

        $pk = $this->orm->getSchema()->define($this->role, SchemaInterface::PRIMARY_KEY);

        return new Reference($this->role, [
            $pk => $primary,
        ]);
    }

    /**
     * @param P $primary
     * @param CriteriaInterface|null $criteria
     * @return CriteriaInterface
     */
    private function getPrimaryCriteria($primary, ?CriteriaInterface $criteria): CriteriaInterface
    {
        $pk = $this->orm->getSchema()->define($this->role, SchemaInterface::PRIMARY_KEY);

        $ef = ExpressionFactory::new();

        return ($criteria ?? Criteria::new())->where($ef->property($pk, $ef->same($primary)));
    }

    private function findReferenceInHeap(ReferenceInterface $reference): ?object
    {
        return $this->orm->get($reference->__role(), $reference->__scope(), false);
    }
}
