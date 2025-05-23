<?php

namespace Oro\Bundle\ApiBundle\Tests\Functional\Common;

use Oro\Bundle\ApiBundle\Tests\Functional\Environment\Entity\TestActivity as ExtendableEntityWithoutGetter;
use Oro\Bundle\ApiBundle\Tests\Functional\Environment\Entity\TestOrder as Entity;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption as EntityWithIsGetter;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User as ExtendableEntity;
use Oro\Component\EntitySerializer\DataAccessorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class EntityDataAccessorTest extends WebTestCase
{
    private DataAccessorInterface $entityDataAccessor;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->entityDataAccessor = self::getContainer()->get('oro_api.entity_accessor');
    }

    public function testHasGetterForAccessibleFieldForRegularEntity(): void
    {
        self::assertTrue($this->entityDataAccessor->hasGetter(Entity::class, 'poNumber'));
    }

    public function testHasGetterForAccessibleFieldForExtendableEntity(): void
    {
        self::assertTrue($this->entityDataAccessor->hasGetter(ExtendableEntity::class, 'firstName'));
    }

    public function testHasGetterForAccessibleFieldForExtendableEntityWithoutGetter(): void
    {
        self::assertTrue($this->entityDataAccessor->hasGetter(ExtendableEntityWithoutGetter::class, 'name'));
    }

    public function testHasGetterForAccessibleFieldForEntityWithIsGetter(): void
    {
        self::assertTrue($this->entityDataAccessor->hasGetter(EntityWithIsGetter::class, 'default'));
    }

    public function testHasGetterForNotAccessibleFieldForRegularEntity(): void
    {
        self::assertFalse($this->entityDataAccessor->hasGetter(Entity::class, 'undefined'));
    }

    public function testHasGetterForNotAccessibleFieldForExtendableEntity(): void
    {
        self::assertFalse($this->entityDataAccessor->hasGetter(ExtendableEntity::class, 'undefined'));
    }

    public function testHasGetterForNotAccessibleFieldForExtendableEntityWithoutGetter(): void
    {
        self::assertFalse($this->entityDataAccessor->hasGetter(ExtendableEntityWithoutGetter::class, 'undefined'));
    }

    public function testTryGetValueForAccessibleFieldForRegularEntity(): void
    {
        $entity = new Entity();
        $newValue = 'test';
        $entity->setPoNumber($newValue);
        $value = 'prev';
        self::assertTrue($this->entityDataAccessor->tryGetValue($entity, 'poNumber', $value));
        self::assertEquals($newValue, $value);
    }

    public function testTryGetValueForAccessibleFieldForExtendableEntity(): void
    {
        $entity = new ExtendableEntity();
        $newValue = 'test';
        $entity->setFirstName($newValue);
        $value = 'prev';
        self::assertTrue($this->entityDataAccessor->tryGetValue($entity, 'firstName', $value));
        self::assertEquals($newValue, $value);
    }

    public function testTryGetValueForAccessibleFieldForExtendableEntityWithoutGetter(): void
    {
        $entity = new ExtendableEntityWithoutGetter();
        $newValue = 'test';
        $entity->name = $newValue;
        $value = 'prev';
        self::assertTrue($this->entityDataAccessor->tryGetValue($entity, 'name', $value));
        self::assertEquals($newValue, $value);
    }

    public function testTryGetValueForAccessibleFieldForEntityWithIsGetter(): void
    {
        $entity = new EntityWithIsGetter('code', 'name', 'id');
        $entity->setDefault(true);
        $value = false;
        self::assertTrue($this->entityDataAccessor->tryGetValue($entity, 'default', $value));
        self::assertTrue($value);
    }

    public function testTryGetValueForNotAccessibleFieldForRegularEntity(): void
    {
        $entity = new Entity();
        $value = 'prev';
        self::assertFalse($this->entityDataAccessor->tryGetValue($entity, 'undefined', $value));
        self::assertEquals('prev', $value);
    }

    public function testTryGetValueForNotAccessibleFieldForExtendableEntity(): void
    {
        $entity = new ExtendableEntity();
        $value = 'prev';
        self::assertFalse($this->entityDataAccessor->tryGetValue($entity, 'undefined', $value));
        self::assertEquals('prev', $value);
    }

    public function testTryGetValueForNotAccessibleFieldForExtendableEntityWithoutGetter(): void
    {
        $entity = new ExtendableEntityWithoutGetter();
        $value = 'prev';
        self::assertFalse($this->entityDataAccessor->tryGetValue($entity, 'undefined', $value));
        self::assertEquals('prev', $value);
    }
}
