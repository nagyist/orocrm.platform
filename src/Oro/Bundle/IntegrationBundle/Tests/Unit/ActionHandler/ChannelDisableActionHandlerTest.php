<?php

namespace Oro\Bundle\IntegrationBundle\Tests\Unit\ActionHandler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\IntegrationBundle\ActionHandler\ChannelDisableActionHandler;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ChannelDisableActionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var ChannelDisableActionHandler */
    private $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new ChannelDisableActionHandler($this->entityManager);
    }

    public function testHandleAction()
    {
        $this->entityManager->expects(self::once())
            ->method('flush');

        $channel = new Channel();
        $channel->setEnabled(true);

        self::assertTrue($this->handler->handleAction($channel));
        self::assertFalse($channel->isEnabled());
        self::assertTrue($channel->getPreviouslyEnabled());
    }
}
