<?php

declare(strict_types=1);

namespace spaceonfire\Criteria\Expression;

use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Constraint\Same;

class ExpressionFactoryTest extends TestCase
{
    private ?\spaceonfire\Criteria\Expression\ExpressionFactory $factory = null;

    protected function setUp(): void
    {
        $this->factory = new ExpressionFactory();
    }

    public function testKey(): void
    {
        $expression = $this->factory->key('key', $innerExpr = $this->factory->null());
        self::assertEquals('[key]', (string)$expression->getPropertyPath());
        self::assertEquals($innerExpr, $expression->getExpression());
    }

    public function testProperty(): void
    {
        $expression = $this->factory->property('key.chain', $innerExpr = $this->factory->null());
        self::assertEquals('key.chain', (string)$expression->getPropertyPath());
        self::assertEquals($innerExpr, $expression->getExpression());
    }

    public function testMagicCall(): void
    {
        $expression = $this->factory->null();
        self::assertInstanceOf(Same::class, $expression);
        self::assertNull($expression->getComparedValue());
    }

    public function testMagicCallFail(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Call to an undefined method spaceonfire\Criteria\Expression\ExpressionFactory::unknown()'
        );
        $this->factory->unknown();
    }
}
