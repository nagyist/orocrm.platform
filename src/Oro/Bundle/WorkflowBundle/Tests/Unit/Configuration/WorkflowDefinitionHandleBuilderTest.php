<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Oro\Bundle\WorkflowBundle\Configuration\Handler\ConfigurationHandlerInterface;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowDefinitionConfigurationBuilder;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowDefinitionHandleBuilder;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use PHPUnit\Framework\TestCase;

class WorkflowDefinitionHandleBuilderTest extends TestCase
{
    public function testBuildFromRawConfiguration(): void
    {
        $rawConfiguration = ['name' => 'test_workflow'];
        $handledConfiguration = ['name' => 'test_workflow', 'label' => 'Test Workflow'];
        $processedConfiguration = ['name' => 'test_workflow', 'label' => 'Test Workflow', 'is_system' => false];

        $workflowDefinition = new WorkflowDefinition();
        $workflowDefinition->setName($processedConfiguration['name']);

        $handler = $this->createMock(ConfigurationHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($rawConfiguration)
            ->willReturn($handledConfiguration);

        $configuration = $this->createMock(WorkflowConfiguration::class);
        $configuration->expects($this->once())
            ->method('processConfiguration')
            ->with($handledConfiguration)
            ->willReturn($processedConfiguration);

        $configurationBuilder = $this->createMock(WorkflowDefinitionConfigurationBuilder::class);
        $configurationBuilder->expects($this->once())
            ->method('buildOneFromConfiguration')
            ->with($processedConfiguration['name'], $processedConfiguration)
            ->willReturn($workflowDefinition);

        $handleBuilder = new WorkflowDefinitionHandleBuilder(
            $configuration,
            $configurationBuilder,
            [$handler]
        );
        $this->assertEquals($workflowDefinition, $handleBuilder->buildFromRawConfiguration($rawConfiguration));
    }
}
