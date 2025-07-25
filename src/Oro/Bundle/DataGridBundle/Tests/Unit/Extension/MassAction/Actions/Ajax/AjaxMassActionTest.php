<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Extension\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;
use PHPUnit\Framework\TestCase;

class AjaxMassActionTest extends TestCase
{
    private AjaxMassAction $action;

    #[\Override]
    protected function setUp(): void
    {
        $this->action = new AjaxMassAction();
    }

    /**
     * @dataProvider setOptionsDataProvider
     */
    public function testSetOptions(array $source, array $expected): void
    {
        $this->action->setOptions(ActionConfiguration::create($source));

        $actual = $this->action->getOptions();
        foreach ($expected as $name => $value) {
            $this->assertEquals($value, $actual->offsetGet($name));
        }
    }

    public function setOptionsDataProvider(): array
    {
        return [
            'confirmation is empty' => [
                'source' => [
                    'handler' => 'test.handler',
                ],
                'expected' => [
                    'confirmation' => true,
                ],
            ],
            'confirmation is false' => [
                'source' => [
                    'handler' => 'test.handler',
                    'confirmation' => false,
                ],
                'expected' => [
                    'confirmation' => false,
                ],
            ],
        ];
    }
}
