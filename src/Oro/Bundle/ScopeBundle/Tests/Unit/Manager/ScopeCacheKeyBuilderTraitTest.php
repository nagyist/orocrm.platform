<?php

namespace Oro\Bundle\ScopeBundle\Tests\Unit\Manager;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Oro\Bundle\ScopeBundle\Manager\ScopeCacheKeyBuilderInterface;
use Oro\Bundle\ScopeBundle\Model\ScopeCriteria;
use Oro\Bundle\ScopeBundle\Tests\Unit\Stub\StubEntity;
use Oro\Bundle\ScopeBundle\Tests\Unit\Stub\TestScopeCacheKeyBuilder;
use PHPUnit\Framework\TestCase;

class ScopeCacheKeyBuilderTraitTest extends TestCase
{
    private function getInnerBuilder(ScopeCriteria $criteria, ?string $cacheKey): ScopeCacheKeyBuilderInterface
    {
        $innerBuilder = $this->createMock(ScopeCacheKeyBuilderInterface::class);
        $innerBuilder->expects($this->once())
            ->method('getCacheKey')
            ->with($this->identicalTo($criteria))
            ->willReturn($cacheKey);

        return $innerBuilder;
    }

    public function testGetCacheKeyWhenInnerBuilderReturnsNull(): void
    {
        $criteria = new ScopeCriteria(
            ['testParam' => 123],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, null));
        $this->assertNull($builder->getCacheKey($criteria));
    }

    public function testGetCacheKeyWithExistingEntity(): void
    {
        $testParam = new StubEntity(123);
        $criteria = new ScopeCriteria(
            ['testParam' => $testParam],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data;testParam=123',
            $builder->getCacheKey($criteria)
        );
    }

    public function testGetCacheKeyWithNewEntity(): void
    {
        $testParam = new StubEntity(null);
        $criteria = new ScopeCriteria(
            ['testParam' => $testParam],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data',
            $builder->getCacheKey($criteria)
        );
    }

    public function testGetCacheKeyWithEntityId(): void
    {
        $criteria = new ScopeCriteria(
            ['testParam' => 123],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data;testParam=123',
            $builder->getCacheKey($criteria)
        );
    }

    public function testGetCacheKeyWithEntityNull(): void
    {
        $criteria = new ScopeCriteria(
            ['testParam' => null],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data',
            $builder->getCacheKey($criteria)
        );
    }

    public function testGetCacheKeyWithoutEntity(): void
    {
        $criteria = new ScopeCriteria(
            [],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data',
            $builder->getCacheKey($criteria)
        );
    }

    public function testGetCacheKeyWithArrayOfEntities(): void
    {
        $testParam1 = new StubEntity(1);
        $testParam2 = new StubEntity(2);
        $criteria = new ScopeCriteria(
            ['testParam' => [$testParam1, $testParam2]],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data;testParam=1,2',
            $builder->getCacheKey($criteria)
        );
    }

    public function testGetCacheKeyWithArrayOfEntityIds(): void
    {
        $criteria = new ScopeCriteria(
            ['testParam' => [1, 2]],
            $this->createMock(ClassMetadataFactory::class)
        );
        $builder = new TestScopeCacheKeyBuilder($this->getInnerBuilder($criteria, 'data'));
        $this->assertEquals(
            'data;testParam=1,2',
            $builder->getCacheKey($criteria)
        );
    }
}
