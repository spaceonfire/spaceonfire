<?php

declare(strict_types=1);

namespace spaceonfire\LaminasHydratorBridge;

use Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;
use stdClass;

class StdClassHydratorTest extends TestCase
{
    private ?\spaceonfire\LaminasHydratorBridge\StdClassHydrator $hydrator = null;

    protected function setUp(): void
    {
        $this->hydrator = new StdClassHydrator();
    }

    public function test(): void
    {
        $array = [
            'id' => 30,
            'firstName' => 'Stephen',
        ];

        $this->hydrator->hydrate($array, $object = new stdClass());

        self::assertSame($array, $this->hydrator->extract($object));
    }

    public function testWithFilter(): void
    {
        $this->hydrator->addFilter('firstName', fn ($fieldName) => $fieldName !== 'firstName');

        $object = new stdClass();
        $object->id = 30;
        $object->firstName = 'Stephen';

        self::assertSame([
            'id' => 30,
        ], $this->hydrator->extract($object));
    }

    public function testWithNamingStrategy(): void
    {
        $this->hydrator->setNamingStrategy(new UnderscoreNamingStrategy());

        $object = new stdClass();
        $object->id = 30;
        $object->firstName = 'Stephen';

        self::assertSame([
            'id' => 30,
            'first_name' => 'Stephen',
        ], $this->hydrator->extract($object));
    }
}
