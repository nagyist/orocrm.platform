<?php

namespace Oro\Bundle\GaufretteBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\GaufretteBundle\DependencyInjection\Compiler\ConfigureGaufretteFileManagersPass;
use Oro\Bundle\GaufretteBundle\FileManager;
use Oro\Bundle\GaufretteBundle\Tests\Unit\Fixtures\CustomFileManager;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConfigureGaufretteFileManagersPassTest extends TestCase
{
    private ExtendedContainerBuilder $container;
    private ConfigureGaufretteFileManagersPass $compiler;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = new ExtendedContainerBuilder();
        $this->compiler = new ConfigureGaufretteFileManagersPass();

        $this->container->register('oro_gaufrette.file_manager', FileManager::class)
            ->setAbstract(true);
    }

    private function registerServiceWithParent(string $id, string $parentId): Definition
    {
        return $this->container->setDefinition($id, new ChildDefinition($parentId));
    }

    public function testNoFilesystemMapService(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', []);

        $this->compiler->process($this->container);
    }

    public function testPrivateDirectoryAwareFileManager(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [['useSubDirectory', [true]]],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testPublicDirectoryAwareFileManager(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'public']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [['useSubDirectory', [true]]],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testNotDirectoryAwareFileManager(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'adapter1']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testAbstractFileManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "file_manager1" service must be abstract.');

        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setAbstract(true)
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);
    }

    public function testDirectoryAwareFileManagerWhenFilesystemArgumentIsDicParameter(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->container->setParameter('file_manager1.filesystem', 'test_fs');
        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['%file_manager1.filesystem%']);

        $this->compiler->process($this->container);

        self::assertSame(
            [['useSubDirectory', [true]]],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testCustomDirectoryAwareFileManager(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setClass(CustomFileManager::class)
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [['useSubDirectory', [true]]],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testCustomDirectoryAwareFileManagerWhenClassNameIsDicParameter(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->container->setParameter('file_manager1.class', CustomFileManager::class);
        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setClass('%file_manager1.class%')
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [['useSubDirectory', [true]]],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testFileManagerWithInvalidClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The class of the "file_manager1" service must be "%s" or has this class as one of its parents.',
            FileManager::class
        ));

        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setClass(\stdClass::class)
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);
    }

    public function testFileManagerWithInvalidFirstArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The first argument of the "file_manager1" service must be the name of a Gaufrette filesystem.'
        );

        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments([false]);

        $this->compiler->process($this->container);
    }

    public function testFileManagerWithoutArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The first argument of the "file_manager1" service must be the name of a Gaufrette filesystem.'
        );

        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager');

        $this->compiler->process($this->container);
    }

    public function testFileManagerWithUndefinedFilesystem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The Gaufrette filesystem "fs3" is used by the "file_manager1" service is not defined.'
            . ' Known filesystems: fs1, fs2.'
        );

        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['fs1' => ['adapter' => 'private'], 'fs2' => ['adapter' => 'public']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['fs3']);

        $this->compiler->process($this->container);
    }

    public function testNotDirectoryAwareFileManagerWasChangedToDirectoryAwareFileManager(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'adapter1']]],
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [['useSubDirectory', [true]]],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }

    public function testDirectoryAwareFileManagerWasChangedToNotDirectoryAwareFileManager(): void
    {
        $this->container->setExtensionConfig('knp_gaufrette', [
            ['filesystems' => ['test_fs' => ['adapter' => 'private']]],
            ['filesystems' => ['test_fs' => ['adapter' => 'adapter1']]]
        ]);

        $this->registerServiceWithParent('file_manager1', 'oro_gaufrette.file_manager')
            ->setArguments(['test_fs']);

        $this->compiler->process($this->container);

        self::assertSame(
            [],
            $this->container->getDefinition('file_manager1')->getMethodCalls()
        );
    }
}
