<?php

declare(strict_types=1);

namespace spaceonfire\Container\Factory;

use spaceonfire\Common\Factory\StaticConstructorInterface;
use spaceonfire\Container\Exception\ContainerException;
use spaceonfire\Container\FactoryOptionsInterface;
use spaceonfire\Container\InvokerOptionsInterface;

final class FactoryOptions implements FactoryOptionsInterface, StaticConstructorInterface
{
    private ?string $staticConstructor = null;

    /**
     * @var array<string,string>
     */
    private array $argumentAliasMap = [];

    /**
     * @var array<string,string>
     */
    private array $argumentTagMap = [];

    /**
     * @var array<string,mixed>
     */
    private array $arguments = [];

    /**
     * @var array<array{string,InvokerOptionsInterface|null}>
     */
    private array $methodCalls = [];

    /**
     * @param array<string,mixed> $arguments
     */
    private function __construct(array $arguments = [])
    {
        foreach ($arguments as $argument => $value) {
            $this->addArgument($argument, $value);
        }
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param FactoryOptionsInterface|array<string,mixed>|null|mixed $options
     * @return FactoryOptionsInterface|null
     */
    public static function wrap($options): ?FactoryOptionsInterface
    {
        if ($options instanceof FactoryOptionsInterface) {
            return $options;
        }

        if (\is_array($options)) {
            // TODO: deprecate using array as options.
            return new self($options);
        }

        if (null === $options) {
            return null;
        }

        throw new ContainerException(\sprintf(
            'Wrong options format. Expected instance of %s or array of arguments, %s given.',
            FactoryOptionsInterface::class,
            \get_debug_type($options)
        ));
    }

    public function getStaticConstructor(): ?string
    {
        return $this->staticConstructor;
    }

    public function setStaticConstructor(string $constructor): self
    {
        $this->staticConstructor = $constructor;

        return $this;
    }

    public function getArgumentAlias(string $argument): ?string
    {
        return $this->argumentAliasMap[$argument] ?? null;
    }

    public function setArgumentAlias(string $argument, string $alias): self
    {
        $this->argumentAliasMap[$argument] = $alias;

        return $this;
    }

    public function getArgumentTag(string $argument): ?string
    {
        return $this->argumentTagMap[$argument] ?? null;
    }

    public function setArgumentTag(string $argument, string $tag): self
    {
        $this->argumentTagMap[$argument] = $tag;

        return $this;
    }

    public function addArgument(string $argument, $value): self
    {
        $this->arguments[$argument] = $value;

        return $this;
    }

    public function getArgument(string $argument)
    {
        return $this->arguments[$argument] ?? null;
    }

    public function hasArgument(string $argument): bool
    {
        return isset($this->arguments[$argument]) || \array_key_exists($argument, $this->arguments);
    }

    public function addMethodCall(string $method, ?InvokerOptionsInterface $options = null): self
    {
        $this->methodCalls[] = [$method, $options];

        return $this;
    }

    public function getMethodCalls(): iterable
    {
        return $this->methodCalls;
    }
}
