<?php

declare(strict_types=1);

namespace spaceonfire\Type\Factory;

use spaceonfire\Type\CollectionType;
use spaceonfire\Type\Exception\TypeNotSupportedException;
use spaceonfire\Type\Type;

final class CollectionTypeFactory implements TypeFactoryInterface
{
    use TypeFactoryTrait;

    /**
     * @var TypeFactoryInterface
     */
    private $iterableTypeFactory;

    /**
     * CollectionTypeFactory constructor.
     * @param TypeFactoryInterface|null $iterableTypeFactory
     */
    public function __construct(?TypeFactoryInterface $iterableTypeFactory = null)
    {
        if (null === $iterableTypeFactory) {
            $iterableTypeFactory = new CompositeTypeFactory(...[
                new InstanceOfTypeFactory(),
                new PartialSupportTypeFactory(new BuiltinTypeFactory(), function (string $type): bool {
                    return in_array($type, ['array', 'iterable'], true);
                }),
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

        if (!isset($typeParts['value'])) {
            return false;
        }

        if (!$this->parent->supports($typeParts['value'])) {
            return false;
        }

        if (
            isset($typeParts['iterable']) &&
            !$this->iterableTypeFactory->supports($typeParts['iterable'])
        ) {
            return false;
        }

        if (isset($typeParts['key']) && !$this->parent->supports($typeParts['key'])) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function make(string $type): Type
    {
        if (!$this->supports($type)) {
            throw new TypeNotSupportedException($type, CollectionType::class);
        }

        /** @var array $parsed */
        $parsed = $this->parseType($type);

        $parsed['value'] = $this->parent->make($parsed['value']);
        $parsed['key'] = $parsed['key'] ? $this->parent->make($parsed['key']) : null;
        $parsed['iterable'] = $parsed['iterable'] ? $this->iterableTypeFactory->make($parsed['iterable']) : null;

        return new CollectionType($parsed['value'], $parsed['key'], $parsed['iterable']);
    }

    /**
     * @param string $type
     * @return array<string,string|null>|null
     */
    private function parseType(string $type): ?array
    {
        $type = $this->removeWhitespaces($type);

        $result = [
            'iterable' => null,
            'key' => null,
            'value' => null,
        ];

        if (strpos($type, '[]') === strlen($type) - 2) {
            $result['value'] = substr($type, 0, -2) ?: null;
            return $result;
        }

        if (
            (0 < $openPos = strpos($type, '<')) &&
            (strpos($type, '>') === strlen($type) - 1)
        ) {
            $result['iterable'] = substr($type, 0, $openPos);
            [$key, $value] = explode(',', substr($type, $openPos + 1, -1)) + [null, null];

            if (!$value && !$key) {
                return null;
            }

            if (null === $value) {
                $value = $key;
                $key = null;
            }

            $result['key'] = $key ?: null;
            $result['value'] = $value ?: null;

            return $result;
        }

        return null;
    }
}
