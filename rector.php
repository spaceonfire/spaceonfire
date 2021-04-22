<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $sources = glob(__DIR__ . '/src/*/src');
    $tests = glob(__DIR__ . '/src/*/tests');

    $parameters->set(
        Option::PATHS,
        array_merge(
            [
                __DIR__ . '/bin',
            ],
            $sources,
            $tests,
        )
    );

    $parameters->set(Option::SETS, [
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
        SetList::PRIVATIZATION,
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    $parameters->set(Option::SKIP, [
        \Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector::class => array_merge($tests, [
            __DIR__ . '/src/ValueObject/src/',
            __DIR__ . '/src/DataSource/src/Exceptions/',
            __DIR__ . '/src/DataSource/src/Bridge/CycleOrm/Mapper/',
            __DIR__ . '/src/DataSource/src/Bridge/CycleOrm/Query/CycleQuery.php',
        ]),
        \Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector::class => [
            __DIR__ . '/src/DataSource/src/Bridge/CycleOrm/Query/CycleQuery.php',
        ],
        \Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector::class,
        \Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector::class => array_merge($tests, [
            __DIR__ . '/src/DataSource/src/Query/AbstractExpressionVisitor.php',
            __DIR__ . '/src/DataSource/src/Bridge/CycleOrm/Query/CycleQueryExpressionVisitor.php',
        ]),
    ]);
};
