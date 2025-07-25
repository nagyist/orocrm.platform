<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\AccessRule;

use Oro\Bundle\SecurityBundle\AccessRule\AvailableOwnerAccessRule;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\AccessDenied;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Comparison;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\CompositeExpression;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Path;
use Oro\Bundle\SecurityBundle\AccessRule\Expr\Value;
use Oro\Bundle\SecurityBundle\ORM\Walker\AccessRuleWalker;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclConditionDataBuilderInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Tests\Unit\Fixtures\Models\CMS\CmsAddress;
use Oro\Bundle\SecurityBundle\Tests\Unit\Fixtures\Models\CMS\CmsUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AvailableOwnerAccessRuleTest extends TestCase
{
    private AclConditionDataBuilderInterface&MockObject $builder;
    private AvailableOwnerAccessRule $rule;

    #[\Override]
    protected function setUp(): void
    {
        $this->builder = $this->createMock(AclConditionDataBuilderInterface::class);

        $ownershipMetadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $ownershipMetadataProvider->expects($this->any())
            ->method('getMetadata')
            ->with(CmsUser::class)
            ->willReturn(new OwnershipMetadata(
                'USER',
                'owner',
                'owner_id',
                'organization',
                'organization_id'
            ));

        $this->rule = new AvailableOwnerAccessRule($this->builder, $ownershipMetadataProvider);
    }

    public function testIsApplicableWithNotRootEntity(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, \stdClass::class, 'e', 'CREATE', false);
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->assertFalse($this->rule->isApplicable($criteria));
    }

    public function testIsApplicableWithRootEntityButWithoutTargetEntityClass(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, \stdClass::class, 'e', 'CREATE', true);

        $this->assertFalse($this->rule->isApplicable($criteria));
    }

    public function testIsApplicableWithRootEntityAndTargetEntityClass(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, \stdClass::class, 'e', 'CREATE', true);
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->assertTrue($this->rule->isApplicable($criteria));
    }

    public function testProcessOnEntityWithFullAccess(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn([]);

        $this->rule->process($criteria);
        $this->assertNull($criteria->getExpression());
    }

    public function testProcessOnEntityWithNoAccess(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn([null, null, null, null, null, null]);

        $this->rule->process($criteria);
        $this->assertInstanceOf(AccessDenied::class, $criteria->getExpression());
    }

    public function testProcessOnEntityWithSingleOwnerRestriction(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn(['owner', 130, null, null, null, null]);

        $this->rule->process($criteria);
        $this->assertEquals(
            new Comparison(new Path('id', 'owner'), '=', new Value(130)),
            $criteria->getExpression()
        );
    }

    public function testProcessOnEntityWithArrayOwnerRestriction(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn(['owner', [5,7,6], null, null, null, null]);

        $this->rule->process($criteria);
        $this->assertEquals(
            new Comparison(new Path('id', 'owner'), 'IN', new Value([5,7,6])),
            $criteria->getExpression()
        );
    }

    public function testProcessOnEntityWithOrganizationRestriction(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn([null, null, 'organization', 1, true]);

        $this->rule->process($criteria);
        $this->assertEquals(
            new Comparison(new Path('organization', 'owner'), '=', new Value(1)),
            $criteria->getExpression()
        );
    }

    public function testProcessOnEntityWithOwnerAndOrganizationRestriction(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn(['owner', [5,7,6], 'organization', 1, false]);

        $this->rule->process($criteria);
        $this->assertEquals(
            new CompositeExpression(
                'AND',
                [
                    new Comparison(new Path('id', 'owner'), 'IN', new Value([5,7,6])),
                    new Comparison(new Path('organization', 'owner'), '=', new Value(1))
                ]
            ),
            $criteria->getExpression()
        );
    }

    public function testProcessOnEntityWithOwnerAndOrganizationRestrictionAndCurrentOwnerOption(): void
    {
        $criteria = new Criteria(AccessRuleWalker::ORM_RULES_TYPE, CmsUser::class, 'owner', 'CREATE');
        $criteria->setOption(AvailableOwnerAccessRule::TARGET_ENTITY_CLASS, CmsAddress::class);
        $criteria->setOption(AvailableOwnerAccessRule::CURRENT_OWNER, 10);

        $this->builder->expects($this->once())
            ->method('getAclConditionData')
            ->with(CmsAddress::class, 'CREATE')
            ->willReturn(['owner', [5,7,6], 'organization', 1, false]);

        $this->rule->process($criteria);
        $this->assertEquals(
            new CompositeExpression(
                'OR',
                [
                    new CompositeExpression(
                        'AND',
                        [
                            new Comparison(new Path('id', 'owner'), 'IN', new Value([5,7,6])),
                            new Comparison(new Path('organization', 'owner'), '=', new Value(1))
                        ]
                    ),
                    new Comparison(new Path('id', 'owner'), '=', new Value(10))
                ]
            ),
            $criteria->getExpression()
        );
    }
}
