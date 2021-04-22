<?php

declare(strict_types=1);

namespace spaceonfire\CommandBus\Mapping\Method;

final class StaticMethodNameMapping implements MethodNameMappingInterface
{
    private string $methodName;

    /**
     * StaticMethodNameMapping constructor.
     * @param string $methodName
     */
    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @inheritDoc
     */
    public function getMethodName(string $commandClassName): string
    {
        return $this->methodName;
    }
}
