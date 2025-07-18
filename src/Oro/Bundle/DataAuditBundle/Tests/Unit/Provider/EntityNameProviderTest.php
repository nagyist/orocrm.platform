<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Provider\EntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityNameProviderTest extends TestCase
{
    private ManagerRegistry&MockObject $doctrine;
    private EntityNameResolver&MockObject $entityNameResolver;
    private EntityNameProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->entityNameResolver = $this->createMock(EntityNameResolver::class);

        $this->provider = new EntityNameProvider($this->doctrine, $this->entityNameResolver);
    }

    /**
     * @dataProvider resolvedNameDataProvider
     */
    public function testGetEntityNameFromEntityNameResolver(string $resolverName, string $expected): void
    {
        $entityClass = '\stdObject';
        $entityId = 1;
        $entity = new \stdClass();

        $em = $this->createMock(ObjectManager::class);
        $em->expects($this->once())
            ->method('find')
            ->with($entityClass, $entityId)
            ->willReturn($entity);

        $this->doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->with($entityClass)
            ->willReturn($em);

        $this->entityNameResolver->expects($this->once())
            ->method('getName')
            ->with($entity)
            ->willReturn($resolverName);

        $this->assertEquals($expected, $this->provider->getEntityName(Audit::class, $entityClass, $entityId));
    }

    public function resolvedNameDataProvider(): array
    {
        return [
            'less than 255 symbols' => ['test name', 'test name'],
            'more than 255 symbols' => [
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eget quam lacus. Vestibulum ' .
                'quis erat at sapien aliquet placerat. Nunc maximus nec sapien vitae mattis. Nulla ultricies metus ' .
                'at est semper rutrum. Sed vulputate purus id turpis consectetur, eget sagittis tellus aliquet. ',
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eget quam lacus. Vestibulum ' .
                'quis erat at sapien aliquet placerat. Nunc maximus nec sapien vitae mattis. Nulla ultricies metus ' .
                'at est semper rutrum. Sed vulputate purus id turpis consecte'
            ],
            'more than 255 symbols UTF-8' => [
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付',
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付' .
                '票驗驗後付',
            ],
        ];
    }
}
