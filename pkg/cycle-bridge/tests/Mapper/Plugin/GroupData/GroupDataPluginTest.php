<?php

declare(strict_types=1);

namespace spaceonfire\Bridge\Cycle\Mapper\Plugin\GroupData;

use spaceonfire\Bridge\Cycle\AbstractTestCase;
use spaceonfire\Bridge\Cycle\Fixtures\OrmCapsule;
use spaceonfire\Bridge\Cycle\Fixtures\Todo\TodoItem;
use spaceonfire\Bridge\Cycle\Mapper\Plugin\DispatcherMapperPlugin;
use spaceonfire\Bridge\Cycle\Mapper\Plugin\ExtractAfterEvent;
use spaceonfire\Bridge\Cycle\Mapper\Plugin\HydrateBeforeEvent;
use spaceonfire\ValueObject\Date\DateTimeImmutableValue;
use Symfony\Component\EventDispatcher\EventDispatcher;

class GroupDataPluginTest extends AbstractTestCase
{
    /**
     * @dataProvider ormCapsuleProvider
     */
    public function testPlugin(OrmCapsule $capsule): void
    {
        $entity = new TodoItem(null, 'FooBar');

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new GroupDataPlugin(new GroupDataHandler($capsule->orm())));
        $mapperPlugin = new DispatcherMapperPlugin($eventDispatcher);

        $createdAt = DateTimeImmutableValue::from('2021-09-18 20:00:00');
        $updatedAt = DateTimeImmutableValue::from('2021-09-19 20:00:00');

        /** @var HydrateBeforeEvent $event */
        $event = $mapperPlugin->dispatch(new HydrateBeforeEvent($entity, [
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'createdBy' => null,
            'updatedBy' => null,
        ]));

        self::assertSame([
            'blame' => [
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
                'createdBy' => null,
                'updatedBy' => null,
            ],
        ], $event->getData());

        /** @var HydrateBeforeEvent $event */
        $event = $mapperPlugin->dispatch(new ExtractAfterEvent($entity, [
            'blame' => [
                'createdAt' => $createdAt,
                'updatedAt' => $updatedAt,
                'createdBy' => null,
                'updatedBy' => null,
            ],
        ]));

        self::assertSame([
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'createdBy' => null,
            'updatedBy' => null,
        ], $event->getData());
    }
}
