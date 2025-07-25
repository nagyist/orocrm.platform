<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Entity;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class AuditTest extends TestCase
{
    public function testUser(): void
    {
        $user = new User();
        $audit = new Audit();

        $this->assertEmpty($audit->getUser());

        $audit->setUser($user);

        $this->assertNotEmpty($audit->getUser());
    }

    public function testObjectId(): void
    {
        $audit = new Audit();

        $this->assertNull($audit->getObjectId());

        $audit->setObjectId(42);

        $this->assertEquals(42, $audit->getObjectId());

        $audit->setObjectId('string_id');

        $this->assertEquals('string_id', $audit->getObjectId());
    }

    public function testObjectName(): void
    {
        $audit = new Audit();
        $name = 'LoggedObject';

        $this->assertEmpty($audit->getObjectName());

        $audit->setObjectName($name);

        $this->assertEquals($name, $audit->getObjectName());
    }

    public function testFieldsShouldBeEmptyWhenNewInstanceIsCreated(): void
    {
        $audit = new Audit();
        $this->assertEmpty($audit->getFields());
    }

    public function testCreateFieldShouldAddNewFieldToAudit(): void
    {
        $audit = new Audit();
        $audit->addField(new AuditField('field', 'integer', 1, 0));

        $this->assertCount(1, $audit->getFields());
        $field = $audit->getFields()->first();
        $this->assertEquals('integer', $field->getDataType());
        $this->assertEquals(1, $field->getNewValue());
        $this->assertEquals(0, $field->getOldValue());
    }

    public function testShouldSetNowAsLoggedAtIfNotPassed(): void
    {
        $audit = new Audit();
        $audit->setLoggedAt();

        $this->assertGreaterThan(new \DateTime('now - 10 seconds'), $audit->getLoggedAt());
        $this->assertLessThan(new \DateTime('now + 10 seconds'), $audit->getLoggedAt());
    }

    public function testShouldSetPassedLoggedAtDate(): void
    {
        $loggedAt = new \DateTime('2012-11-10 01:02:03+0000');

        $audit = new Audit();
        $audit->setLoggedAt($loggedAt);

        $this->assertSame($loggedAt, $audit->getLoggedAt());
    }

    public function testLimitOwnerDescription(): void
    {
        $descr = str_pad('a', 300);
        $audit = new Audit();
        $audit->setOwnerDescription($descr);

        $this->assertSame(255, mb_strlen($audit->getOwnerDescription()));
    }
}
