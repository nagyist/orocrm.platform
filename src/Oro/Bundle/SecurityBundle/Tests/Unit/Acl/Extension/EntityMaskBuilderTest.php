<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use PHPUnit\Framework\TestCase;

class EntityMaskBuilderTest extends TestCase
{
    private const PATTERN_ALL_OFF = '................................';

    private const OFF = '.';
    private const ON = '*';

    private int $identity;
    private EntityMaskBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->identity = $this->getIdentity(random_int(0, 20));
        $this->builder = new EntityMaskBuilder($this->identity, ['VIEW', 'CREATE', 'EDIT', 'DELETE', 'ASSIGN']);
    }

    /**
     * @dataProvider maskConstantProvider
     */
    public function testMaskConstant(string $mask): void
    {
        $count = 0;
        $bitmask = decbin($this->builder->getMask($mask) & EntityMaskBuilder::REMOVE_SERVICE_BITS);
        for ($i = 0; $i < strlen($bitmask); $i++) {
            if ('1' === $bitmask[$i]) {
                $count++;
            }
        }

        $this->assertEquals(1, $count, sprintf('Each mask must set one and only one bit. Bitmask: %s', $bitmask));
    }

    /**
     * @dataProvider addAndRemoveProvider
     */
    public function testAddAndRemove(string|int $maskName, int $mask): void
    {
        $this->builder->add($maskName);
        $this->assertEquals($mask | $this->identity, $this->builder->get());

        $this->builder->remove($maskName);
        $this->assertEquals($this->identity, $this->builder->get());
    }

    public function testGetPattern(): void
    {
        $builder = new EntityMaskBuilder(0, ['VIEW', 'CREATE', 'EDIT', 'DELETE', 'ASSIGN']);
        $this->assertEquals(self::PATTERN_ALL_OFF, $builder->getPattern());

        $builder->add('view_basic');
        $expected = substr(self::PATTERN_ALL_OFF, 0, -1) . self::ON;
        $this->assertEquals($expected, $builder->getPattern());

        $offOn = self::OFF . self::ON;
        $onOn = self::ON . self::ON;

        $builder->add('view_local');
        $expected = str_replace($offOn, $onOn, $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_deep');
        $expected = str_replace($offOn, $onOn, $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_global');
        $expected = str_replace($offOn, $onOn, $expected);
        $this->assertEquals($expected, $builder->getPattern());

        $builder->add('view_system');
        $expected = str_replace($offOn, $onOn, $expected);
        $this->assertEquals($expected, $builder->getPattern());
    }

    public function testGetPatternWithUndefinedMask(): void
    {
        $expected = self::ON . substr(self::PATTERN_ALL_OFF, 1);

        $this->assertEquals($expected, EntityMaskBuilder::getPatternFor((int)2147483648));
    }

    public function testReset(): void
    {
        $this->assertEquals($this->identity, $this->builder->get());

        $this->builder->add('view_basic');
        $this->assertTrue($this->builder->get() > $this->identity);

        $this->builder->reset();
        $this->assertEquals($this->identity, $this->builder->get());
    }

    /**
     * @dataProvider groupProvider
     */
    public function testGroup(string $groupName, int $expectedMask): void
    {
        $groupMask = $this->builder->getMask($groupName);

        $this->assertEquals(
            $expectedMask | $this->identity,
            $groupMask,
            'Actual: ' . EntityMaskBuilder::getPatternFor($groupMask)
        );
    }

    public static function addAndRemoveProvider(): array
    {
        return [
            ['VIEW_BASIC', 1],
            ['VIEW_LOCAL', 2],
            ['VIEW_DEEP', 4],
            ['VIEW_GLOBAL', 8],
            ['VIEW_SYSTEM', 16],
            ['CREATE_BASIC', 32],
            ['CREATE_LOCAL', 64],
            ['CREATE_DEEP', 128],
            ['CREATE_GLOBAL', 256],
            ['CREATE_SYSTEM', 512],
            ['EDIT_BASIC', 1024],
            ['EDIT_LOCAL', 2048],
            ['EDIT_DEEP', 4096],
            ['EDIT_GLOBAL', 8192],
            ['EDIT_SYSTEM', 16384],
            ['view_basic', 1],
            ['view_local', 2],
            ['view_deep', 4],
            ['view_global', 8],
            ['view_system', 16],
            ['create_basic', 32],
            ['create_local', 64],
            ['create_deep', 128],
            ['create_global', 256],
            ['create_system', 512],
            ['edit_basic', 1024],
            ['edit_local', 2048],
            ['edit_deep', 4096],
            ['edit_global', 8192],
            ['edit_system', 16384],
            [1, 1],
            [2, 2],
            [4, 4],
            [8, 8],
            [16, 16],
            [32, 32],
            [64, 64],
            [128, 128],
            [256, 256],
            [512, 512],
            [1024, 1024],
            [2048, 2048],
            [4096, 4096],
            [8192, 8192],
            [16384, 16384],
            [PHP_INT_MAX, 33554431],
        ];
    }

    public static function maskConstantProvider(): array
    {
        return [
            ['MASK_VIEW_BASIC'],
            ['MASK_CREATE_BASIC'],
            ['MASK_EDIT_BASIC'],
            ['MASK_VIEW_LOCAL'],
            ['MASK_CREATE_LOCAL'],
            ['MASK_EDIT_LOCAL'],
            ['MASK_VIEW_DEEP'],
            ['MASK_CREATE_DEEP'],
            ['MASK_EDIT_DEEP'],
            ['MASK_VIEW_GLOBAL'],
            ['MASK_CREATE_GLOBAL'],
            ['MASK_EDIT_GLOBAL'],
            ['MASK_VIEW_SYSTEM'],
            ['MASK_CREATE_SYSTEM'],
            ['MASK_EDIT_SYSTEM']
        ];
    }

    public static function groupProvider(): array
    {
        return [
            'GROUP_BASIC' => [
                'groupName' => 'GROUP_BASIC',
                'expectedMask' => 1 << 0 | 1 << 5 | 1 << 10 | 1 << 15 | 1 << 20
            ],
            'GROUP_LOCAL' => [
                'groupName' => 'GROUP_LOCAL',
                'expectedMask' => 1 << 1 | 1 << 6 | 1 << 11 | 1 << 16 | 1 << 21
            ],
            'GROUP_DEEP' => [
                'groupName' => 'GROUP_DEEP',
                'expectedMask' => 1 << 2 | 1 << 7 | 1 << 12 | 1 << 17 | 1 << 22
            ],
            'GROUP_GLOBAL' => [
                'groupName' => 'GROUP_GLOBAL',
                'expectedMask' => 1 << 3 | 1 << 8 | 1 << 13 | 1 << 18 | 1 << 23
            ],
            'GROUP_SYSTEM' => [
                'groupName' => 'GROUP_SYSTEM',
                'expectedMask' => 1 << 4 | 1 << 9 | 1 << 14 | 1 << 19 | 1 << 24
            ],
            'GROUP_VIEW' => [
                'groupName' => 'GROUP_VIEW',
                'expectedMask' => (1 << 5) - 1
            ],
            'GROUP_CREATE' => [
                'groupName' => 'GROUP_CREATE',
                'expectedMask' => (1 << 10) - (1 << 5)
            ],
            'GROUP_EDIT' => [
                'groupName' => 'GROUP_EDIT',
                'expectedMask' => (1 << 15) - (1 << 10)
            ],
            'GROUP_DELETE' => [
                'groupName' => 'GROUP_DELETE',
                'expectedMask' => (1 << 20) - (1 << 15)
            ],
            'GROUP_ASSIGN' => [
                'groupName' => 'GROUP_ASSIGN',
                'expectedMask' => (1 << 25) - (1 << 20)
            ],
            'GROUP_NONE' => [
                'groupName' => 'GROUP_NONE',
                'expectedMask' => 0
            ],
            'GROUP_ALL' => [
                'groupName' => 'GROUP_ALL',
                'expectedMask' => (1 << 25) - 1
            ]
        ];
    }

    private static function getIdentity(int $index): int
    {
        return $index << (count(AccessLevel::$allAccessLevelNames) * EntityMaskBuilder::MAX_PERMISSIONS_IN_MASK);
    }
}
