<?php

declare(strict_types=1);

namespace spaceonfire\Type\Factory;

use spaceonfire\Type\CollectionType;
use spaceonfire\Type\Exception\TypeNotSupportedException;
use spaceonfire\Type\TypeInterface;

final class CollectionTypeFactory implements TypeFactoryInterface
{
    use TypeFactoryTrait;

    /**
     * @var string
     */
    private const ITERABLE = 'iterable';

    /**
     * @var string
     */
    private const VALUE = 'value';

    /**
     * @var string
     */
    private const KEY = 'key';

    private TypeFactoryInterface $iterableTypeFactory;

    /**
     * CollectionTypeFactory constructor.
     * @param TypeFactoryInterface|null $iterableTypeFactory
     */
    public function __construct(?TypeFactoryInterface $iterableTypeFactory = null)
    {
        if (null === $iterableTypeFactory) {
            $iterableTypeFactory = new CompositeTypeFactory(...[
                new InstanceOfTypeFactory(),
                new PartialSupportTypeFactory(new BuiltinTypeFactory(), fn (string $type): bool => in_array(
                    $type,
                    ['array', self::ITERABLE],
                    true
                )),
            ]);
        }

        $this->iterableTypeFactory = $iterableTypeFactory;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $type): bool
    {
        if (null === $this->parent) {
            return false;
        }

        $this->iterableTypeFactory->setParent($this->parent);

        $typeParts = $this->parseType($type);

        if (null === $typeParts) {
            return false;
        }

        if (!isset($typeParts[self::VALUE])) {
            return false;
        }

        if (!$this->parent->supports($typeParts[self::VALUE])) {
            return false;
        }

        if (
            isset($typeParts[self::ITERABLE]) &&
            !$this->iterableTypeFactory->supports($typeParts[self::ITERABLE])
        ) {
            return false;
        }

        if (isset($typeParts[self::KEY]) && !$this->parent->supports($typeParts[self::KEY])) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function make(string $type): TypeInterface
    {
        if (!$this->supports($type)) {
            throw new TypeNotSupportedException($type, CollectionType::class);
        }

        /** @var array $parsed */
        $parsed = $this->parseType($type);

        $parsed[self::VALUE] = $this->parent->make($parsed[self::VALUE]);
        $parsed[self::KEY] = $parsed[self::KEY]
            ? $this->parent->make($parsed[self::KEY])
            : null;
        $parsed[self::ITERABLE] = $parsed[self::ITERABLE]
            ? $this->iterableTypeFactory->make($parsed[self::ITERABLE])
            : null;

        return new CollectionType($parsed[self::VALUE], $parsed[self::KEY], $parsed[self::ITERABLE]);
    }

    /**
     * @param string $type
     * @return array<string,string|null>|null
     */
    private function parseType(string $type): ?array
    {
        $type = $this->removeWhitespaces($type);

        $result = [
            self::ITERABLE => null,
            self::KEY => null,
            self::VALUE => null,
        ];

        if (strpos($type, '[]') === strlen($type) - 2) {
            $result[self::VALUE] = substr($type, 0, -2) ?: null;
            return $result;
        }

        if (
            (0 < $openPos = strpos($type, '<')) &&
            (strpos($type, '>') === strlen($type) - 1)
        ) {
            $result[self::ITERABLE] = substr($type, 0, $openPos);
            [$key, $value] = explode(',', substr($type, $openPos + 1, -1)) + [null, null];

            if (!$value && !$key) {
                return null;
            }

            if (null === $value) {
                $value = $key;
                $key = null;
            }

            $result[self::KEY] = $key ?: null;
            $result[self::VALUE] = $value ?: null;

            return $result;
        }

        return null;
    }
}
