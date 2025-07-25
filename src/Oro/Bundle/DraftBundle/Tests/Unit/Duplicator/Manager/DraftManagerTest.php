<?php

namespace Oro\Bundle\DraftBundle\Tests\Unit\Duplicator\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\DraftBundle\Duplicator\DraftContext;
use Oro\Bundle\DraftBundle\Duplicator\Extension\DraftSourceExtension;
use Oro\Bundle\DraftBundle\Duplicator\ExtensionProvider;
use Oro\Bundle\DraftBundle\Manager\DraftManager;
use Oro\Bundle\DraftBundle\Manager\Publisher;
use Oro\Bundle\DraftBundle\Tests\Unit\Stub\DraftableEntityStub;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DraftManagerTest extends TestCase
{
    use EntityTrait;

    private DraftManager $draftManager;
    private Publisher&MockObject $publisher;
    private ContextAccessor $contextAccessor;

    #[\Override]
    protected function setUp(): void
    {
        $this->contextAccessor = new ContextAccessor();
        $this->publisher = $this->createMock(Publisher::class);
        $extensionProvider = new ExtensionProvider($this->getExtensions());
        $this->draftManager = new DraftManager($extensionProvider, $this->contextAccessor, $this->publisher);
    }

    public function testManageDraftWithoutContext(): void
    {
        $source = $this->getEntity(DraftableEntityStub::class);
        $draft = $this->draftManager->createDraft(new DraftableEntityStub());
        $this->assertEquals($source, $draft->getDraftSource());
    }

    public function testCreateDraft(): void
    {
        $context = new DraftContext();
        $source = $this->getEntity(DraftableEntityStub::class);
        $draft = $this->draftManager->createDraft(new DraftableEntityStub(), $context);

        $this->assertEquals(DraftManager::ACTION_CREATE_DRAFT, $context->offsetGet('action'));
        $this->assertEquals($source, $draft->getDraftSource());
    }

    public function testCreatePublication(): void
    {
        $this->publisher->expects($this->once())
            ->method('create')
            ->willReturnArgument(0);

        $context = new DraftContext();
        $source = $this->getEntity(DraftableEntityStub::class);
        $publication = $this->draftManager->createPublication(new DraftableEntityStub(), $context);

        $this->assertEquals(DraftManager::ACTION_PUBLISH_DRAFT, $context->offsetGet('action'));
        $this->assertEquals($source, $publication->getDraftSource());
    }

    private function getExtensions(): iterable
    {
        $extension = new DraftSourceExtension();

        return new ArrayCollection([$extension]);
    }
}
