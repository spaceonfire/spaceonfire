<?php

declare(strict_types=1);

namespace spaceonfire\Common\Kernel;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use spaceonfire\Container\CompositeContainer;
use spaceonfire\Container\DefinitionContainer;
use spaceonfire\Container\FactoryContainer;
use spaceonfire\Container\RawValueHolder;
use spaceonfire\Container\ServiceProvider\AbstractServiceProvider;

class KernelTest extends TestCase
{
    private function factory(?ContainerInterface $container = null, bool $debugModeEnabled = false): AbstractKernel
    {
        return new class($container, $debugModeEnabled) extends AbstractKernel {
            protected function loadServiceProviders(): iterable
            {
                return [
                    new class extends AbstractServiceProvider {
                        public function provides(): array
                        {
                            return [
                                'foo',
                            ];
                        }

                        public function register(): void
                        {
                            $this->getContainer()->define('foo', new RawValueHolder('bar'));
                        }
                    }
                ];
            }
        };
    }

    public function testIsDebugModeEnabled(): void
    {
        $nonDebugKernel = $this->factory();
        self::assertFalse($nonDebugKernel->isDebugModeEnabled());

        $debugKernel = $this->factory(null, true);
        self::assertTrue($debugKernel->isDebugModeEnabled());
    }

    public function testDependenciesRegisteredInContainer(): void
    {
        $kernel = $this->factory(
            new CompositeContainer(new DefinitionContainer(), new FactoryContainer())
        );

        self::assertSame($kernel, $kernel->getContainer()->get('kernel'));
        self::assertSame($kernel, $kernel->getContainer()->get(get_class($kernel)));
        self::assertFalse($kernel->getContainer()->get('kernel.debug'));
        self::assertSame('bar', $kernel->getContainer()->get('foo'));
    }
}
