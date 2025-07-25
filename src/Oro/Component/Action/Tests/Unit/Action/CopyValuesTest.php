<?php

namespace Oro\Component\Action\Tests\Unit\Action;

use Oro\Component\Action\Action\CopyValues;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Tests\Unit\Fixtures\ItemStub;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyPath;

class CopyValuesTest extends TestCase
{
    private CopyValues $action;

    #[\Override]
    protected function setUp(): void
    {
        $this->action = new CopyValues(new ContextAccessor());
        $this->action->setDispatcher($this->createMock(EventDispatcher::class));
    }

    public function testInitialize(): void
    {
        $this->action->initialize([
            new PropertyPath('attr'),
            new PropertyPath('prop1'),
            []
        ]);

        self::assertEquals(
            new PropertyPath('attr'),
            ReflectionUtil::getPropertyValue($this->action, 'attribute')
        );
        self::assertEquals(
            [new PropertyPath('prop1'), []],
            ReflectionUtil::getPropertyValue($this->action, 'options')
        );
    }

    public function testInitializeWithEmptyOptions(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Attribute and data parameters are required');
        $this->action->initialize([]);
    }

    public function testInitializeWithIncorrectAttribute(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Attribute must be valid property definition');
        $this->action->initialize(['var1', 'var2']);
    }

    /**
     * @dataProvider executeProvider
     */
    public function testExecute(array $inputData, array $options, array $expectedData): void
    {
        $context = new ItemStub($inputData);

        $this->action->initialize($options);
        $this->action->execute($context);

        self::assertEquals($expectedData, $context->getData());
    }

    public function executeProvider(): array
    {
        return [
            'object attribute' => [
                'input' => [
                    'attr' => (object)['key1' => 'value1', 'key2' => null, 'key3' => null],
                    'prop' => ['key2' => 'value2'],
                    'ignored_prop' => (object)['key4' => 'value4'],
                ],
                'options' => [
                    new PropertyPath('attr'),
                    new PropertyPath('prop'),
                    new PropertyPath('ignored_prop'),
                    ['key3' => 'value3'],
                ],
                'expected' => [
                    'attr' => (object)['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'],
                    'prop' => ['key2' => 'value2'],
                    'ignored_prop' => (object)['key4' => 'value4'],
                ],
            ],
            'array attribute' => [
                'input' => [
                    'attr' => ['key1' => 'value1'],
                    'prop' => ['key2' => 'value2'],
                    'ignored_prop' => 'property value',
                ],
                'options' => [
                    new PropertyPath('attr'),
                    new PropertyPath('prop'),
                    new PropertyPath('ignored_prop'),
                    ['key3' => 'value3'],
                ],
                'expected' => [
                    'attr' => ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'],
                    'prop' => ['key2' => 'value2'],
                    'ignored_prop' => 'property value',
                ],
            ],
            'null attribute' => [
                'input' => [
                    'prop' => ['key2' => 'value2'],
                    'ignored_prop' => 123,
                ],
                'options' => [
                    new PropertyPath('attr'),
                    new PropertyPath('prop'),
                    new PropertyPath('ignored_prop'),
                    ['key3' => 'value3'],
                ],
                'expected' => [
                    'attr' => ['key2' => 'value2', 'key3' => 'value3'],
                    'prop' => ['key2' => 'value2'],
                    'ignored_prop' => 123,
                ],
            ],
        ];
    }
}
