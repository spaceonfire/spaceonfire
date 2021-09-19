<?php

declare(strict_types=1);

namespace spaceonfire\Container\Factory\Reflection;

use PHPUnit\Framework\TestCase;
use spaceonfire\Container\Factory\FactoryOptions;
use spaceonfire\Container\FactoryContainer;
use spaceonfire\Container\Fixtures\A;
use spaceonfire\Container\Fixtures\ArrayContainer;
use spaceonfire\Container\Fixtures\B;
use spaceonfire\Container\Fixtures\MyClass;
use spaceonfire\Container\Fixtures\PHP8\UnionTypes;

class ReflectionInvokerTest extends TestCase
{
    private function makeContainer(?array $services = null): ArrayContainer
    {
        $services ??= [
            A::class => new A(),
            B::class => new B(new A()),
        ];
        return new ArrayContainer($services);
    }

    public function testInvoker(): void
    {
        $factory = new ReflectionFactoryAggregate();
        $invoker = new ReflectionInvoker();
        $container = new FactoryContainer($factory, $invoker);
        $invoker->setContainer($container);

        self::assertSame('foo', $invoker->invoke([new MyClass(), 'methodA']));
        self::assertSame('bar', $invoker->invoke(MyClass::class . '::staticMethodB'));
        self::assertSame(42, $invoker->invoke(new class {
            public function __invoke()
            {
                return 42;
            }
        }));

        self::assertSame(
            42,
            $invoker->invoke('\\spaceonfire\\Container\\Fixtures\\intval', FactoryOptions::wrap([
                'value' => '42',
                'base' => 10,
            ]))
        );
    }

    public function testInvokeUnionTypeRequired(): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Test case requires PHP 8.');
        }

        $invoker = new ReflectionInvoker($this->makeContainer());

        $result = $invoker->invoke([new UnionTypes(), 'methodAB']);

        self::assertCount(1, $result);
        self::assertInstanceOf(A::class, $result[0]);

        $invoker = new ReflectionInvoker($this->makeContainer([
            B::class => new B(new A()),
        ]));

        $result = $invoker->invoke([new UnionTypes(), 'methodAB']);

        self::assertCount(1, $result);
        self::assertInstanceOf(B::class, $result[0]);
    }

    public function testInvokeUnionTypeOptional(): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Test case requires PHP 8.');
        }

        $invoker = new ReflectionInvoker($this->makeContainer());
        $result = $invoker->invoke([new UnionTypes(), 'methodNullableAB']);

        self::assertCount(1, $result);
        self::assertInstanceOf(A::class, $result[0]);

        $invoker = new ReflectionInvoker($this->makeContainer([]));
        $result = $invoker->invoke([new UnionTypes(), 'methodNullableAB']);

        self::assertCount(1, $result);
        self::assertNull($result[0]);
    }

    public function testInvokeUnionTypeUnknown(): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Test case requires PHP 8.');
        }

        $invoker = new ReflectionInvoker($this->makeContainer());
        $result = $invoker->invoke([new UnionTypes(), 'methodABUnknown']);

        self::assertCount(1, $result);
        self::assertInstanceOf(A::class, $result[0]);
    }

    public function testInvokeMixedType(): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Test case requires PHP 8.');
        }

        $invoker = new ReflectionInvoker($this->makeContainer());
        $result = $invoker->invoke([new UnionTypes(), 'methodMixed']);

        self::assertCount(1, $result);
        self::assertNull($result[0]);
    }
}
