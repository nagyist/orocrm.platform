<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\EventListener;

use Oro\Bundle\ImapBundle\EventListener\LoginListener;
use Oro\Bundle\ImapBundle\OriginSyncCredentials\SyncCredentialsIssueManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Unit\Stub\AbstractUserStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var SyncCredentialsIssueManager|\PHPUnit\Framework\MockObject\MockObject */
    private $syncCredentialsManager;

    /** @var LoginListener */
    private $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->syncCredentialsManager = $this->createMock(SyncCredentialsIssueManager::class);

        $this->listener = new LoginListener($this->syncCredentialsManager);
    }

    public function testOnLoginWithAnonUser()
    {
        $token = new UsernamePasswordToken($this->createMock(UserInterface::class), 'test');
        $event = new InteractiveLoginEvent($this->createMock(Request::class), $token);

        $this->syncCredentialsManager->expects($this->never())
            ->method('processInvalidOriginsForUser');

        $this->listener->onLogin($event);
    }

    public function testOnLoginWithNonUserExtendsUserInToken()
    {
        $user = new AbstractUserStub();
        $token = new UsernamePasswordToken($user, 'key', ['user']);
        $event = new InteractiveLoginEvent($this->createMock(Request::class), $token);

        $this->syncCredentialsManager->expects($this->never())
            ->method('processInvalidOriginsForUser');

        $this->listener->onLogin($event);
    }

    public function testOnLoginWitUserInToken()
    {
        $user = new User();
        $token = new UsernamePasswordToken($user, 'key', ['user']);
        $event = new InteractiveLoginEvent($this->createMock(Request::class), $token);

        $this->syncCredentialsManager->expects($this->once())
            ->method('processInvalidOriginsForUser')
            ->with($user);

        $this->listener->onLogin($event);
    }
}
