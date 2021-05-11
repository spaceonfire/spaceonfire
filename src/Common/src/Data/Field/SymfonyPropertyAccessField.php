<?php

declare(strict_types=1);

namespace spaceonfire\Common\Data\Field;

use spaceonfire\Common\Data\FieldInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

final class SymfonyPropertyAccessField implements FieldInterface
{
    private static ?PropertyAccessorInterface $defaultPropertyAccessor = null;

    /**
     * @var PropertyPath<array-key>
     */
    private PropertyPath $propertyPath;

    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @param string|PropertyPath<array-key> $propertyPath
     * @param PropertyAccessorInterface|null $propertyAccessor
     */
    public function __construct($propertyPath, ?PropertyAccessorInterface $propertyAccessor = null)
    {
        if (!class_exists(PropertyAccess::class)) {
            // TODO: throw missing package exception
            throw new \RuntimeException('Install symfony/property-access');
        }

        if (!$propertyPath instanceof PropertyPath) {
            $propertyPath = new PropertyPath($propertyPath);
        }

        $this->propertyPath = $propertyPath;
        $this->propertyAccessor = $propertyAccessor ?? self::getDefaultPropertyAccessor();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return (string)$this->propertyPath;
    }

    /**
     * @inheritDoc
     */
    public function extract($element)
    {
        if (!$this->propertyAccessor->isReadable($element, $this->propertyPath)) {
            return null;
        }

        return $this->propertyAccessor->getValue($element, $this->propertyPath);
    }

    private static function getDefaultPropertyAccessor(): PropertyAccessorInterface
    {
        return self::$defaultPropertyAccessor ??= PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidIndex()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
    }
}
