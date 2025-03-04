<?php

namespace Oro\Bundle\ApiBundle\Tests\Unit\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Oro\Bundle\ApiBundle\Filter\CompositeIdentifierFilter;
use Oro\Bundle\ApiBundle\Filter\FilterOperator;
use Oro\Bundle\ApiBundle\Filter\FilterValue;
use Oro\Bundle\ApiBundle\Metadata\EntityMetadata;
use Oro\Bundle\ApiBundle\Metadata\FieldMetadata;
use Oro\Bundle\ApiBundle\Request\EntityIdTransformerInterface;
use Oro\Bundle\ApiBundle\Request\EntityIdTransformerRegistry;
use Oro\Bundle\ApiBundle\Request\RequestType;

class CompositeIdentifierFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var EntityIdTransformerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $entityIdTransformerRegistry;

    /** @var CompositeIdentifierFilter */
    private $filter;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityIdTransformerRegistry = $this->createMock(EntityIdTransformerRegistry::class);

        $this->filter = new CompositeIdentifierFilter('string');
        $this->filter->setEntityIdTransformerRegistry($this->entityIdTransformerRegistry);
        $this->filter->setSupportedOperators([FilterOperator::EQ, FilterOperator::NEQ]);
    }

    public function testApplyFilterForNullFilterValue()
    {
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $criteria = new Criteria();
        $this->filter->apply($criteria, null);

        self::assertNull($criteria->getWhereExpression());
    }

    public function testApplyFilterForNullIdentifier()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The value must not be NULL.');

        $filterValue = new FilterValue('id', null);
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $criteria = new Criteria();
        $this->filter->apply($criteria, $filterValue);
    }

    public function testApplyFilterForUnsupportedOperator()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The operator ">" is not supported.');

        $filterValue = new FilterValue('id', 'id1=1;renamedId2=2', '>');
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $criteria = new Criteria();
        $this->filter->apply($criteria, $filterValue);
    }

    public function testApplyFilterForEqualOperatorAndOneId()
    {
        $filterValue = new FilterValue('id', 'id1=1;renamedId2=2');
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');
        $metadata->setIdentifierFieldNames(['id1', 'renamedId2']);
        $metadata->addField(new FieldMetadata('id1'));
        $metadata->addField(new FieldMetadata('renamedId2'))->setPropertyPath('id2');
        $entityIdTransformer = $this->createMock(EntityIdTransformerInterface::class);

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $this->entityIdTransformerRegistry->expects(self::once())
            ->method('getEntityIdTransformer')
            ->with(self::identicalTo($requestType))
            ->willReturn($entityIdTransformer);
        $entityIdTransformer->expects(self::once())
            ->method('reverseTransform')
            ->with('id1=1;renamedId2=2', self::identicalTo($metadata))
            ->willReturn(['id1' => 1, 'renamedId2' => 2]);

        $criteria = new Criteria();
        $this->filter->apply($criteria, $filterValue);

        self::assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new Comparison('id1', Comparison::EQ, 1),
                    new Comparison('id2', Comparison::EQ, 2)
                ]
            ),
            $criteria->getWhereExpression()
        );
    }

    public function testApplyFilterForNotEqualOperatorAndOneId()
    {
        $filterValue = new FilterValue('id', 'id1=1;renamedId2=2', FilterOperator::NEQ);
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');
        $metadata->setIdentifierFieldNames(['id1', 'renamedId2']);
        $metadata->addField(new FieldMetadata('id1'));
        $metadata->addField(new FieldMetadata('renamedId2'))->setPropertyPath('id2');
        $entityIdTransformer = $this->createMock(EntityIdTransformerInterface::class);

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $this->entityIdTransformerRegistry->expects(self::once())
            ->method('getEntityIdTransformer')
            ->with(self::identicalTo($requestType))
            ->willReturn($entityIdTransformer);
        $entityIdTransformer->expects(self::once())
            ->method('reverseTransform')
            ->with('id1=1;renamedId2=2', self::identicalTo($metadata))
            ->willReturn(['id1' => 1, 'renamedId2' => 2]);

        $criteria = new Criteria();
        $this->filter->apply($criteria, $filterValue);

        self::assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_OR,
                [
                    new Comparison('id1', Comparison::NEQ, 1),
                    new Comparison('id2', Comparison::NEQ, 2)
                ]
            ),
            $criteria->getWhereExpression()
        );
    }

    public function testApplyFilterForEqualOperatorAndSeveralIds()
    {
        $filterValue = new FilterValue('id', ['id1=1;renamedId2=2', 'id1=3;renamedId2=4']);
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');
        $metadata->setIdentifierFieldNames(['id1', 'renamedId2']);
        $metadata->addField(new FieldMetadata('id1'));
        $metadata->addField(new FieldMetadata('renamedId2'))->setPropertyPath('id2');
        $entityIdTransformer = $this->createMock(EntityIdTransformerInterface::class);

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $this->entityIdTransformerRegistry->expects(self::once())
            ->method('getEntityIdTransformer')
            ->with(self::identicalTo($requestType))
            ->willReturn($entityIdTransformer);
        $entityIdTransformer->expects(self::exactly(2))
            ->method('reverseTransform')
            ->willReturnMap([
                ['id1=1;renamedId2=2', $metadata, ['id1' => 1, 'renamedId2' => 2]],
                ['id1=3;renamedId2=4', $metadata, ['id1' => 3, 'renamedId2' => 4]]
            ]);

        $criteria = new Criteria();
        $this->filter->apply($criteria, $filterValue);

        self::assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_OR,
                [
                    new CompositeExpression(
                        CompositeExpression::TYPE_AND,
                        [
                            new Comparison('id1', Comparison::EQ, 1),
                            new Comparison('id2', Comparison::EQ, 2)
                        ]
                    ),
                    new CompositeExpression(
                        CompositeExpression::TYPE_AND,
                        [
                            new Comparison('id1', Comparison::EQ, 3),
                            new Comparison('id2', Comparison::EQ, 4)
                        ]
                    )
                ]
            ),
            $criteria->getWhereExpression()
        );
    }

    public function testApplyFilterForNotEqualOperatorAndSeveralIds()
    {
        $filterValue = new FilterValue('id', ['id1=1;renamedId2=2', 'id1=3;renamedId2=4'], FilterOperator::NEQ);
        $requestType = new RequestType([RequestType::REST]);
        $metadata = new EntityMetadata('Test\Entity');
        $metadata->setIdentifierFieldNames(['id1', 'renamedId2']);
        $metadata->addField(new FieldMetadata('id1'));
        $metadata->addField(new FieldMetadata('renamedId2'))->setPropertyPath('id2');
        $entityIdTransformer = $this->createMock(EntityIdTransformerInterface::class);

        $this->filter->setRequestType($requestType);
        $this->filter->setMetadata($metadata);

        $this->entityIdTransformerRegistry->expects(self::once())
            ->method('getEntityIdTransformer')
            ->with(self::identicalTo($requestType))
            ->willReturn($entityIdTransformer);
        $entityIdTransformer->expects(self::exactly(2))
            ->method('reverseTransform')
            ->willReturnMap([
                ['id1=1;renamedId2=2', $metadata, ['id1' => 1, 'renamedId2' => 2]],
                ['id1=3;renamedId2=4', $metadata, ['id1' => 3, 'renamedId2' => 4]]
            ]);

        $criteria = new Criteria();
        $this->filter->apply($criteria, $filterValue);

        self::assertEquals(
            new CompositeExpression(
                CompositeExpression::TYPE_AND,
                [
                    new CompositeExpression(
                        CompositeExpression::TYPE_OR,
                        [
                            new Comparison('id1', Comparison::NEQ, 1),
                            new Comparison('id2', Comparison::NEQ, 2)
                        ]
                    ),
                    new CompositeExpression(
                        CompositeExpression::TYPE_OR,
                        [
                            new Comparison('id1', Comparison::NEQ, 3),
                            new Comparison('id2', Comparison::NEQ, 4)
                        ]
                    )
                ]
            ),
            $criteria->getWhereExpression()
        );
    }
}
