<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\WorkflowBundle\Datagrid\EmailNotificationDatagridListener;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowTranslationHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailNotificationDatagridListenerTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private EmailNotificationDatagridListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($key, array $parameters, $domain) {
                $this->assertEmpty($parameters);
                $this->assertEquals(WorkflowTranslationHelper::TRANSLATION_DOMAIN, $domain);

                return sprintf('*%s*', $key);
            });

        $this->listener = new EmailNotificationDatagridListener($this->translator);
    }

    public function testOnBuildBefore(): void
    {
        $event = $this->getBuildBeforeEvent();

        $this->listener->onBuildBefore($event);

        $this->assertEquals(
            [
                'filters' => [
                    'columns' => [
                        'workflow_definition' => [
                            'type' => 'workflow'
                        ]
                    ]
                ]
            ],
            $event->getConfig()->toArray()
        );
    }

    public function testOnResultAfter(): void
    {
        $record1 = $this->getResultRecord();
        $record2 = $this->getResultRecord('test_workflow');
        $record3 = $this->getResultRecord('test_workflow', 'test_transition');
        $record4 = $this->getResultRecord(null, 'test_transition');

        $event = $this->getOrmResultAfterEvent([$record1, $record2, $record3, $record4]);

        $this->listener->onResultAfter($event);

        $this->assertEquals($this->getResultRecord(), $record1);
        $this->assertEquals($this->getResultRecord('*oro.workflow.test_workflow.label*'), $record2);
        $this->assertEquals(
            $this->getResultRecord(
                '*oro.workflow.test_workflow.label*',
                '*oro.workflow.test_workflow.transition.test_transition.label* (test_transition)'
            ),
            $record3
        );
        $this->assertEquals($this->getResultRecord(null, 'test_transition'), $record4);
    }

    private function getBuildBeforeEvent(): BuildBefore&MockObject
    {
        $event = $this->createMock(BuildBefore::class);
        $event->expects($this->any())
            ->method('getConfig')
            ->willReturn(DatagridConfiguration::create([]));

        return $event;
    }

    private function getOrmResultAfterEvent(array $records = []): OrmResultAfter&MockObject
    {
        $event = $this->createMock(OrmResultAfter::class);
        $event->expects($this->any())
            ->method('getRecords')
            ->willReturn($records);

        return $event;
    }

    /**
     * @param string $workflowName
     * @param string $transitionName
     * @return ResultRecord
     */
    private function getResultRecord($workflowName = null, $transitionName = null)
    {
        $data = [];

        if ($workflowName) {
            $data['workflow_definition_target_field'] = $workflowName;
        }

        if ($transitionName) {
            $data['workflow_transition_name'] = $transitionName;
        }

        return new ResultRecord($data);
    }
}
