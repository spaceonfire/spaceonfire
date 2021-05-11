<?php

declare(strict_types=1);

namespace spaceonfire\Common\Data\Field;

use spaceonfire\Common\Data\FieldInterface;
use Yiisoft\Arrays\ArrayHelper;

final class YiiArrayField implements FieldInterface
{
    private string $field;

    /**
     * @var array-key|array<array-key>|\Closure|null
     */
    private $extractKey;

    /**
     * @param string $field
     * @param array-key|array<array-key>|\Closure|null $extractKey
     */
    public function __construct(string $field, $extractKey = null)
    {
        if (!class_exists(ArrayHelper::class)) {
            // TODO: throw missing package exception
            throw new \RuntimeException('Install yiisoft/arrays');
        }

        $this->field = $field;
        $this->extractKey = $extractKey;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     */
    public function extract($element)
    {
        return ArrayHelper::getValue($element, $this->extractKey ?? $this->field);
    }
}
