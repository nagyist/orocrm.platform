<?php

namespace Oro\Bundle\MaintenanceBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Command\CommandTestingTrait;

class MaintenanceLockUnlockCommandTest extends WebTestCase
{
    use CommandTestingTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
    }

    public function testMaintenanceLock(): void
    {
        $this->assertResponseCode(200);

        $commandTester = $this->doExecuteCommand('oro:maintenance:lock');

        $this->assertOutputContains($commandTester, 'Maintenance mode is turned on.');
        $this->assertResponseCode(503);
    }

    /**
     * @depends testMaintenanceLock
     */
    public function testMaintenanceUnlock(): void
    {
        $commandTester = $this->doExecuteCommand('oro:maintenance:unlock');

        $this->assertOutputContains($commandTester, 'Maintenance mode is turned off.');
        $this->assertResponseCode(200);
    }

    public function testMaintenanceLockWhenAlreadyEnabled(): void
    {
        $this->doExecuteCommand('oro:maintenance:lock');

        $commandTester = $this->doExecuteCommand('oro:maintenance:lock');

        $this->assertOutputContains($commandTester, 'Maintenance mode is already enabled.');
        $this->assertSuccessReturnCode($commandTester);
        $this->assertResponseCode(503);

        $this->doExecuteCommand('oro:maintenance:unlock');
    }

    public function testMaintenanceUnlockWhenAlreadyDisabled(): void
    {
        $this->doExecuteCommand('oro:maintenance:unlock');

        $commandTester = $this->doExecuteCommand('oro:maintenance:unlock');

        $this->assertOutputContains($commandTester, 'Maintenance mode is already disabled.');
        $this->assertSuccessReturnCode($commandTester);
        $this->assertResponseCode(200);
    }

    private function assertResponseCode(int $code): void
    {
        $this->client->request('GET', $this->getUrl('oro_user_security_login'));
        $result = $this->client->getResponse();
        self::assertHtmlResponseStatusCodeEquals($result, $code);
    }
}
