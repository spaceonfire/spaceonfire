<?php

/** @noinspection SqlNoDataSourceInspection SqlDialectInspection */

declare(strict_types=1);

namespace spaceonfire\Bridge\Cycle\Select;

use Cycle\ORM\Select;
use spaceonfire\Bridge\Cycle\AbstractTestCase;
use spaceonfire\Bridge\Cycle\Fixtures\OrmCapsule;
use spaceonfire\Bridge\Cycle\Fixtures\User;
use spaceonfire\Bridge\Cycle\Mapper\CyclePropertyExtractor;
use spaceonfire\Criteria\Expression\AbstractExpressionDecorator;
use spaceonfire\Criteria\Expression\ExpressionFactory;
use Webmozart\Expression\Expression;

class CycleExpressionVisitorTest extends AbstractTestCase
{
    private function bootstrapVisitor(OrmCapsule $capsule, string $role): array
    {
        $select = new Select($capsule->orm(), $role);

        $visitor = new CycleExpressionVisitor(new CyclePropertyExtractor($capsule->orm(), $role));

        return [$visitor, $select];
    }

    /**
     * @dataProvider successDataProvider
     * @param OrmCapsule $capsule
     * @param Expression $expression
     * @param string $expectedSql
     */
    public function testDispatch(OrmCapsule $capsule, Expression $expression, string $expectedSql): void
    {
        /**
         * @var CycleExpressionVisitor $visitor
         * @var Select $select
         */
        [$visitor, $select] = $this->bootstrapVisitor($capsule, 'post');

        $select->where($visitor->dispatch($expression));

        $query = $select->buildQuery();
        $query->columns('*');

        $sql = \implode(' ', \array_map('trim', \explode("\n", (string)$query)));

        self::assertEquals($expectedSql, $sql);
    }

    /**
     * @dataProvider errorDataProvider
     * @param OrmCapsule $capsule
     * @param Expression $expression
     */
    public function testDispatchUnknown(OrmCapsule $capsule, Expression $expression): void
    {
        [$visitor] = $this->bootstrapVisitor($capsule, 'post');

        $this->expectException(\InvalidArgumentException::class);
        $visitor->dispatch($expression);
    }

    public function successDataProvider(): \Generator
    {
        $capsule = $this->makeOrmCapsule();
        $ef = ExpressionFactory::new();

        yield [
            $capsule,
            $ef->key('id', $ef->same('value')),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" = \'value\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->same(null))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" IS NOT NULL  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->equals('test'))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" <> \'test\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->in([1, 2, 3])),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" IN (1 ,2 ,3)  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->in([1, 2, 3]))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" NOT IN (1 ,2 ,3)  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->contains('hello')),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" LIKE \'%hello%\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->startsWith('hello')),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" LIKE \'hello%\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->endsWith('hello')),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" LIKE \'%hello\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->contains('hello'))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" NOT LIKE \'%hello%\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->startsWith('hello'))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" NOT LIKE \'hello%\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->endsWith('hello'))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" NOT LIKE \'%hello\'  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->greaterThan(1)),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" > 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->lessThan(1)),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" < 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->greaterThanEqual(1)),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" >= 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->lessThanEqual(1)),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" <= 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->greaterThan(1))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" <= 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->lessThan(1))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" >= 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->greaterThanEqual(1))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" < 1  )',
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->lessThanEqual(1))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" > 1  )',
        ];
        yield [
            $capsule,
            new class($ef->property('id', $ef->not($ef->lessThanEqual(1)))) extends AbstractExpressionDecorator {
            },
            'SELECT * FROM "post" AS "post" WHERE ("post"."id" > 1  )',
        ];
        yield [
            $capsule,
            $ef->andX([
                $ef->key('id', $ef->greaterThan(10)),
                $ef->key('id', $ef->lessThan(100)),
            ]),
            'SELECT * FROM "post" AS "post" WHERE (("post"."id" > 10  )AND ("post"."id" < 100  ) )',
        ];
        yield [
            $capsule,
            $ef->orX([
                $ef->key('id', $ef->greaterThan(10)),
                $ef->key('id', $ef->lessThan(100)),
            ]),
            'SELECT * FROM "post" AS "post" WHERE (("post"."id" > 10  )OR ("post"."id" < 100  ) )',
        ];
        yield [
            $capsule,
            $ef->property('createdAt', $ef->greaterThan(new \DateTimeImmutable('2021-08-25 00:00:00'))),
            'SELECT * FROM "post" AS "post" WHERE ("post"."created_at" > \'2021-08-25T00:00:00+00:00\'  )',
        ];
        yield [
            $capsule,
            $ef->property('author.name', $ef->startsWith('Admin')),
            'SELECT * FROM "post" AS "post" INNER JOIN "user" AS "post_author" ON "post_author"."id" = "post"."author_id" WHERE ("post_author"."name" LIKE \'Admin%\'  )',
        ];
        yield [
            $capsule,
            $ef->property('author', $ef->same($capsule->orm()->make(User::class, [
                'id' => '35a60006-c34a-4c0b-8e9d-7759f6d0c09b',
                'name' => 'Admin User',
            ]))),
            'SELECT * FROM "post" AS "post" INNER JOIN "user" AS "post_author" ON "post_author"."id" = "post"."author_id" WHERE ("post_author"."id" = \'35a60006-c34a-4c0b-8e9d-7759f6d0c09b\'  )',
        ];
        yield [
            $capsule,
            $ef->true(),
            'SELECT * FROM "post" AS "post" WHERE (1 = 1  )',
        ];
        yield [
            $capsule,
            $ef->false(),
            'SELECT * FROM "post" AS "post" WHERE (1 = 0  )',
        ];
    }

    public function errorDataProvider(): \Generator
    {
        $capsule = $this->makeOrmCapsule();
        $ef = ExpressionFactory::new();

        yield [
            $capsule,
            $ef->method('getTitle', [], $ef->same('')),
        ];
        yield [
            $capsule,
            new class($ef->method('getTitle', [], $ef->same(''))) extends AbstractExpressionDecorator {
            },
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->isInstanceOf(self::class)),
        ];
        yield [
            $capsule,
            $ef->property('id', $ef->not($ef->isInstanceOf(self::class))),
        ];
    }
}
