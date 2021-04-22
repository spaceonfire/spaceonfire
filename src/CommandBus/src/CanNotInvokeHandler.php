<?php

declare(strict_types=1);

namespace spaceonfire\CommandBus;

use BadMethodCallException;
use function get_class;

/**
 * Thrown when a specific handler object can not be used on a command object.
 *
 * The most common reason is the receiving method is missing or incorrectly named.
 */
final class CanNotInvokeHandler extends BadMethodCallException implements ExceptionInterface
{
    private object $command;

    private function __construct(object $command, string $message = '')
    {
        parent::__construct($message);

        $this->command = $command;
    }

    public static function forCommand(object $command, string $reason): self
    {
        $type = get_class($command);

        return new self(
            $command,
            sprintf('Could not invoke handler for command %s for reason: %s', $type, $reason)
        );
    }

    /**
     * Returns the command that could not be invoked
     */
    public function getCommand(): object
    {
        return $this->command;
    }
}
