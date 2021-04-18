<?php

declare(strict_types=1);

namespace spaceonfire\Criteria;

use spaceonfire\Criteria\Expression\ExpressionFactory;
use Webmozart\Expression\Expression;

interface CriteriaInterface
{
    /**
     * Getter for `expression` property
     * @return Expression|null
     */
    public function getWhere(): ?Expression;

    /**
     * Setter for `expression` property
     * @param Expression|null $expression
     * @return $this
     */
    public function where(?Expression $expression): CriteriaInterface;

    /**
     * Joins provided expression with current one using conjunction.
     * @param Expression $expression
     * @return $this
     */
    public function andWhere(Expression $expression): CriteriaInterface;

    /**
     * Joins provided expression with current one using disjunction.
     * @param Expression $expression
     * @return $this
     */
    public function orWhere(Expression $expression): CriteriaInterface;

    /**
     * Getter for `orderBy` property
     * @return array<string,int>
     */
    public function getOrderBy(): array;

    /**
     * Setter for `orderBy` property
     * @param array<string,int> $orderBy
     * @return $this
     */
    public function orderBy(array $orderBy): CriteriaInterface;

    /**
     * Getter for `offset` property
     * @return int
     */
    public function getOffset(): int;

    /**
     * Setter for `offset` property
     * @param int|null $offset
     * @return $this
     */
    public function offset(?int $offset): CriteriaInterface;

    /**
     * Getter for `limit` property
     * @return int|null
     */
    public function getLimit(): ?int;

    /**
     * Setter for `limit` property
     * @param int|null $limit
     * @return $this
     */
    public function limit(?int $limit): CriteriaInterface;

    /**
     * Getter for `include` property
     * @return mixed[]
     */
    public function getInclude(): array;

    /**
     * Setter for `include` property
     * @param mixed[] $include
     * @return $this
     */
    public function include(array $include): CriteriaInterface;

    /**
     * Merges parameters from current criteria and provided one
     * @param CriteriaInterface $criteria
     * @return CriteriaInterface
     */
    public function merge(CriteriaInterface $criteria): CriteriaInterface;

    /**
     * Returns expression factory
     * @return ExpressionFactory
     */
    public static function expr(): ExpressionFactory;
}
