<?php

namespace Oro\Bundle\PlatformBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\PlatformBundle\DependencyInjection\Compiler\LazyServicesCompilerPass;
use Oro\Bundle\PlatformBundle\Tests\Unit\DependencyInjection\Fixtures;
use Oro\Component\Config\CumulativeResourceManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LazyServicesCompilerPassTest extends TestCase
{
    public function testShouldMarkServicesAsLazy(): void
    {
        $fooBundle = new Fixtures\FooBundle\FooBundle();
        $barBundle = new Fixtures\BarBundle\BarBundle();

        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([
                $fooBundle->getName() => get_class($fooBundle),
                $barBundle->getName() => get_class($barBundle)
            ]);

        $container = new ContainerBuilder();
        $container->register('foo_service');
        $container->register('bar_service');

        $compiler = new LazyServicesCompilerPass();
        $compiler->process($container);

        self::assertTrue($container->getDefinition('foo_service')->isLazy());
        self::assertTrue($container->getDefinition('bar_service')->isLazy());
        self::assertFalse($container->hasDefinition('not_existing_service'));

        self::assertSame(
            [
                sprintf(
                    '%s: The service "not_existing_service" cannot be marked as lazy due to it does not exist.',
                    LazyServicesCompilerPass::class
                )
            ],
            $container->getCompiler()->getLog()
        );
    }
}
