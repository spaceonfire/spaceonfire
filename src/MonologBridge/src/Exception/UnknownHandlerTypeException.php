<?php

declare(strict_types=1);

namespace spaceonfire\MonologBridge\Exception;

use InvalidArgumentException;

final class UnknownHandlerTypeException extends InvalidArgumentException
{
    private function __construct(string $message = '')
    {
        parent::__construct($message);
    }

    public static function forHandlerType(string $handlerType): self
    {
        return new self(sprintf('No factory for given monolog handler type "%s"', $handlerType));
    }
}
