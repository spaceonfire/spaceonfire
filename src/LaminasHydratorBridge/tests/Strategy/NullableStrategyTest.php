<?php

declare(strict_types=1);

namespace spaceonfire\LaminasHydratorBridge\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use Prophecy\Argument;
use spaceonfire\LaminasHydratorBridge\AbstractTestCase;

class NullableStrategyTest extends AbstractTestCase
{
    /**
     * @return StrategyInterface
     */
    private function mockStrategy(): StrategyInterface
    {
        $strategyMock = $this->prophesize(StrategyInterface::class);
        $strategyMock->hydrate(Argument::any(), Argument::any())
            ->shouldBeCalledTimes(1)
            ->will(fn ($args) => $args[0]);
        $strategyMock->extract(Argument::any(), Argument::any())
            ->shouldBeCalledTimes(1)
            ->will(fn ($args) => $args[0]);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $strategyMock->reveal();
    }

    public function testSimple(): void
    {
        $strategy = new NullableStrategy($this->mockStrategy());

        self::assertSame('foo', $strategy->extract('foo'));
        self::assertNull($strategy->extract(null));
        self::assertSame('foo', $strategy->hydrate('foo', []));
        self::assertNull($strategy->hydrate(null, []));
    }

    public function testCustomNullValuePredicate(): void
    {
        $nullValues = [0, '', null];
        $strategy = new NullableStrategy($this->mockStrategy(), static fn ($value): bool => in_array($value, $nullValues, true));

        self::assertSame('foo', $strategy->extract('foo'));

        foreach ($nullValues as $value) {
            self::assertNull($strategy->extract($value));
        }

        self::assertSame('foo', $strategy->hydrate('foo', []));

        foreach ($nullValues as $value) {
            self::assertNull($strategy->hydrate($value, []));
        }
    }
}
