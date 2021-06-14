<?php

declare(strict_types=1);

namespace spaceonfire\CommandBus\Bridge\SymfonyStopwatch;

use Psr\Log\Test\TestLogger;
use spaceonfire\CommandBus\_Fixtures\Bridge\SymfonyStopwatch\FixtureMayBeProfiledMessage;
use spaceonfire\CommandBus\AbstractTestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilerMiddlewareTest extends AbstractTestCase
{
    public function testExecute(): void
    {
        $middleware = new ProfilerMiddleware(
            $stopwatch = new Stopwatch()
        );

        $message = new FixtureMayBeProfiledMessage();

        $result = $middleware->execute($message, fn ($message) => 'foo');

        $expectedEventName = get_class($message) . '_' . spl_object_hash($message);

        self::assertSame('foo', $result);
        self::assertNotNull($stopwatch->getEvent($expectedEventName));
    }

    public function testExecuteWithLogger(): void
    {
        $middleware = new ProfilerMiddleware(
            $stopwatch = new Stopwatch()
        );

        $middleware->setLogger($logger = new TestLogger());

        $message = new FixtureMayBeProfiledMessage();

        $result = $middleware->execute($message, fn ($message) => 'foo');

        $expectedEventName = get_class($message) . '_' . spl_object_hash($message);
        $event = $stopwatch->getEvent($expectedEventName);

        self::assertSame('foo', $result);
        self::assertNotNull($event);
        self::assertTrue($logger->hasDebug(sprintf('Profiling event %s started', $expectedEventName)));
        self::assertTrue($logger->hasDebug([
            'message' => sprintf('Profiling event %s finished (%s)', $expectedEventName, (string)$event),
            'context' => [
                'event' => $event,
            ],
        ]));
    }

    public function testExecuteWithMayBeProfiledMessage(): void
    {
        $middleware = new ProfilerMiddleware(
            $stopwatch = new Stopwatch()
        );

        $message = new FixtureMayBeProfiledMessage();
        $message->setProfilingEventName('custom_event_name');
        $message->setProfilingCategory('custom_category');

        $result = $middleware->execute($message, fn ($message) => 'foo');

        $event = $stopwatch->getEvent('custom_event_name');

        self::assertSame('foo', $result);
        self::assertNotNull($event);
        self::assertSame('custom_category', $event->getCategory());
    }

    public function testExecuteWithPredicate(): void
    {
        $redMessage = new FixtureMayBeProfiledMessage();
        $redMessage->setProfilingEventName('red_event');

        $greenMessage = new FixtureMayBeProfiledMessage();
        $greenMessage->setProfilingEventName('green_event');

        $predicateProphecy = $this->prophesize(ProfilerMiddlewareMessagePredicateInterface::class);
        $predicateProphecy->__invoke($redMessage)->willReturn(false);
        $predicateProphecy->__invoke($greenMessage)->willReturn(true);

        $middleware = new ProfilerMiddleware(
            $stopwatch = new Stopwatch(),
            $predicateProphecy->reveal()
        );

        $next = static fn (FixtureMayBeProfiledMessage $message) => 'foo';

        $middleware->execute($redMessage, $next);
        $middleware->execute($greenMessage, $next);

        self::assertNotNull($stopwatch->getEvent('green_event'));
        $this->expectException(\LogicException::class);
        $stopwatch->getEvent('red_event');
    }
}
