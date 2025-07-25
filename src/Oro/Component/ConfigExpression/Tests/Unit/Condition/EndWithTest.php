<?php

namespace Oro\Component\ConfigExpression\Tests\Unit\Condition;

use Oro\Component\ConfigExpression\Condition\EndWith;
use Oro\Component\ConfigExpression\ContextAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyPath;

class EndWithTest extends TestCase
{
    protected $condition;

    #[\Override]
    protected function setUp(): void
    {
        $this->condition = new EndWith();
        $this->condition->setContextAccessor(new ContextAccessor());
    }

    /**
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate(array $options, $context, $expectedResult): void
    {
        $this->assertSame($this->condition, $this->condition->initialize($options));
        $this->assertEquals($expectedResult, $this->condition->evaluate($context));
    }

    public function evaluateDataProvider(): array
    {
        $options = ['left' => new PropertyPath('foo'), 'right' => new PropertyPath('bar')];

        return [
            'in_sentence' => [
                'options'        => $options,
                'context'        => ['foo' => 'Here is the word in sentence.', 'bar' => 'word'],
                'expectedResult' => false
            ],
            'at_the_beginning' => [
                'options'        => $options,
                'context'        => ['foo' => 'Word is at the beginning.', 'bar' => 'word'],
                'expectedResult' => false,
            ],
            'at_the_end' => [
                'options'        => $options,
                'context'        => ['foo' => 'At the end is word', 'bar' => 'word'],
                'expectedResult' => true,
            ],
            'case_insensitive' => [
                'options'        => $options,
                'context'        => ['foo' => 'At the end is WoRd', 'bar' => 'word'],
                'expectedResult' => true,
            ],
            'special_characters' => [
                'options'        => $options,
                'context'        => ['foo' => 'Příliš žluťoučký', 'bar' => 'žluťoučký'],
                'expectedResult' => true,
            ],
        ];
    }
}
