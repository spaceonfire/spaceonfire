<?php

declare(strict_types=1);

namespace spaceonfire\DataSource;

use spaceonfire\Collection\CollectionInterface;
use spaceonfire\Criteria\CriteriaInterface;

/**
 * Interface RepositoryInterface
 * @package spaceonfire\DataSource
 *
 * @method mixed|EntityInterface getById($id)
 * @method CollectionInterface getList($criteria)
 */
interface RepositoryInterface
{
    /**
     * Persist entity in storage
     * @param EntityInterface $entity
     * @throws Exceptions\SaveException
     */
    public function save($entity): void;

    /**
     * Removes entity from storage
     * @param EntityInterface $entity
     * @throws Exceptions\RemoveException
     */
    public function remove($entity): void;

    /**
     * Returns entity by its primary field
     * @param mixed $primary
     * @return mixed&EntityInterface
     * @throws Exceptions\NotFoundException
     */
    public function findByPrimary($primary);

    /**
     * Returns entity collection matching provided criteria
     * @param CriteriaInterface $criteria
     * @return CollectionInterface
     */
    public function findAll(?CriteriaInterface $criteria = null): CollectionInterface;

    /**
     * Returns first entity matching provided criteria
     * @param CriteriaInterface $criteria
     * @return mixed|EntityInterface|null
     */
    public function findOne(?CriteriaInterface $criteria = null);

    /**
     * Counts entities matching provided criteria
     * @param CriteriaInterface|null $criteria
     * @return int
     */
    public function count(?CriteriaInterface $criteria = null): int;

    /**
     * Returns mapper for repository's entity
     * @return MapperInterface
     */
    public function getMapper(): MapperInterface;
}