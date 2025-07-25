<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Processor\Transition;

use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Processor\Context\TransitionContext;
use Oro\Bundle\WorkflowBundle\Processor\Transition\CustomFromStartWorkflowDataProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

class CustomFromStartWorkflowDataProcessorTest extends TestCase
{
    private CustomFromStartWorkflowDataProcessor $processor;

    #[\Override]
    protected function setUp(): void
    {
        $this->processor = new CustomFromStartWorkflowDataProcessor();
    }

    public function testSetFormDataAttribute(): void
    {
        $formData = (object)['id' => 42];

        $transition = $this->createMock(Transition::class);
        $transition->expects($this->once())
            ->method('getFormDataAttribute')
            ->willReturn('attribute');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($formData);

        $context = new TransitionContext();
        $context->setTransition($transition);
        $context->setForm($form);
        $context->set(TransitionContext::INIT_DATA, ['other prop' => 'other val']);

        $this->processor->process($context);

        $this->assertEquals(
            [
                'other prop' => 'other val',
                'attribute' => $formData
            ],
            $context->get(TransitionContext::INIT_DATA)
        );
    }

    public function testCreatesArrayForInitDataIfNo(): void
    {
        $transition = $this->createMock(Transition::class);
        $transition->expects($this->once())
            ->method('getFormDataAttribute')
            ->willReturn('attribute');

        $formData = (object)['id' => 42];

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($formData);

        $context = new TransitionContext();
        $context->setTransition($transition);
        $context->setForm($form);
        $context->set(TransitionContext::INIT_DATA, null);

        $this->processor->process($context);

        $this->assertEquals(['attribute' => $formData], $context->get(TransitionContext::INIT_DATA));
    }

    public function testNothingChangedIfAlreadyDefined(): void
    {
        $transition = $this->createMock(Transition::class);
        $transition->expects($this->once())
            ->method('getFormDataAttribute')
            ->willReturn('attribute');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())
            ->method('getData');

        $existentDataAttribute = (object)['id' => 42];

        $context = new TransitionContext();
        $context->setTransition($transition);
        $context->setForm($form);
        $context->set(TransitionContext::INIT_DATA, ['attribute' => $existentDataAttribute]);

        $this->processor->process($context);

        $this->assertEquals(['attribute' => $existentDataAttribute], $context->get(TransitionContext::INIT_DATA));
    }
}
