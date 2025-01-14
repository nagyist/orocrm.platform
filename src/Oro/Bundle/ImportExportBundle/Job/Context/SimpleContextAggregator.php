<?php

namespace Oro\Bundle\ImportExportBundle\Job\Context;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Job\ContextHelper;

/**
 * Creates the context object contains counters summarized by the type from all steps.
 */
class SimpleContextAggregator implements ContextAggregatorInterface
{
    const TYPE = 'simple';

    /** @var ContextRegistry */
    protected $contextRegistry;

    public function __construct(ContextRegistry $contextRegistry)
    {
        $this->contextRegistry = $contextRegistry;
    }

    #[\Override]
    public function getAggregatedContext(JobExecution $jobExecution)
    {
        /** @var ContextInterface $context */
        $context = null;
        $stepExecutions = $jobExecution->getStepExecutions();
        foreach ($stepExecutions as $stepExecution) {
            if (null === $context) {
                $context = $this->contextRegistry->getByStepExecution($stepExecution);
            } else {
                ContextHelper::mergeContextCounters(
                    $context,
                    $this->contextRegistry->getByStepExecution($stepExecution)
                );
            }
        }

        return $context;
    }

    #[\Override]
    public function getType()
    {
        return self::TYPE;
    }
}
