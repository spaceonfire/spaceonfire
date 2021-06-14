<?php

declare(strict_types=1);

namespace spaceonfire\Type\Factory;

use spaceonfire\Type\Exception\TypeNotSupportedException;
use spaceonfire\Type\TypeInterface;

final class CompositeTypeFactory implements TypeFactoryInterface
{
    use TypeFactoryTrait;

    /**
     * @var TypeFactoryInterface[]
     */
    private array $factories;

    /**
     * CompositeTypeFactory constructor.
     * @param TypeFactoryInterface ...$factories
     */
    public function __construct(TypeFactoryInterface ...$factories)
    {
        $this->factories = $factories;
    }

    public static function makeWithDefaultFactories(): self
    {
        return new self(...self::makeDefaultFactories());
    }

    public static function makeDefaultFactories(): iterable
    {
        yield new CollectionTypeFactory();
        yield new GroupTypeFactory();
        yield new ConjunctionTypeFactory();
        yield new DisjunctionTypeFactory();
        yield new InstanceOfTypeFactory();
        yield new BuiltinTypeFactory();
        yield new MixedTypeFactory();
        yield new VoidTypeFactory();
    }

    /**
     * @inheritDoc
     */
    public function supports(string $type): bool
    {
        foreach ($this->factories as $factory) {
            $factory->setParent($this->parent ?? $this);

            if ($factory->supports($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function make(string $type): TypeInterface
    {
        foreach ($this->factories as $factory) {
            $factory->setParent($this->parent ?? $this);

            if ($factory->supports($type)) {
                return $factory->make($type);
            }
        }

        throw new TypeNotSupportedException($type);
    }
}
