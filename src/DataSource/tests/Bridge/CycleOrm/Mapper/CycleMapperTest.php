<?php

declare(strict_types=1);

namespace spaceonfire\DataSource\Bridge\CycleOrm\Mapper;

use Cycle\ORM\ORMInterface;
use Laminas\Hydrator\NamingStrategy\MapNamingStrategy;
use Laminas\Hydrator\Strategy\ClosureStrategy;
use Nette\Utils\Strings;
use spaceonfire\DataSource\Bridge\CycleOrm\AbstractCycleOrmTest;

class CycleMapperTest extends AbstractCycleOrmTest
{
    private static ?\spaceonfire\DataSource\Bridge\CycleOrm\Mapper\BasicCycleMapper $mapper = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$mapper === null) {
            self::$mapper = new class(self::getOrm(), 'user') extends BasicCycleMapper {
                public function __construct(ORMInterface $orm, string $role)
                {
                    parent::__construct($orm, $role);

                    $this->hydrator->setNamingStrategy(MapNamingStrategy::createFromExtractionMap([
                        'name' => 'NAME',
                    ]));

                    $this->hydrator->addStrategy(
                        'name',
                        new ClosureStrategy(
                            static fn ($value) => Strings::upper((string)$value),
                            static fn ($value) => Strings::lower((string)$value)
                        )
                    );
                }
            };
        }
    }

    public function testConvertValueToStorage(): void
    {
        $storageVal = self::$mapper->convertValueToStorage('name', 'Admin User');
        self::assertEquals('ADMIN USER', $storageVal);
    }

    public function testConvertValueToDomain(): void
    {
        $domainVal = self::$mapper->convertValueToDomain('name', 'Admin User');
        self::assertEquals('admin user', $domainVal);
    }

    public function testConvertNameToStorage(): void
    {
        $storageVal = self::$mapper->convertNameToStorage('name');
        self::assertSame('NAME', $storageVal);
    }

    public function testConvertNameToDomain(): void
    {
        $domainVal = self::$mapper->convertNameToDomain('NAME');
        self::assertSame('name', $domainVal);
    }
}
