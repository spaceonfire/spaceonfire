<?php

declare(strict_types=1);

namespace spaceonfire\CommandBus\Mapping;

use RuntimeException;
use spaceonfire\CommandBus\ExceptionInterface;

final class FailedToMapCommand extends RuntimeException implements ExceptionInterface
{
    public static function className(string $commandClassName): self
    {
        return new self('Failed to map the class name for command ' . $commandClassName);
    }

    public static function methodName(string $commandClassName): self
    {
        return new self('Failed to map the method name for command ' . $commandClassName);
    }
}
