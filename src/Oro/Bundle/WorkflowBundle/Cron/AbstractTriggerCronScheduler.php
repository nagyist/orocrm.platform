<?php

namespace Oro\Bundle\WorkflowBundle\Cron;

use Oro\Bundle\CronBundle\Entity\Manager\DeferredScheduler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractTriggerCronScheduler implements LoggerAwareInterface
{
    /** @var DeferredScheduler */
    protected $deferredScheduler;

    public function __construct(DeferredScheduler $deferredScheduler)
    {
        $this->deferredScheduler = $deferredScheduler;
        $this->setLogger(new NullLogger());
    }

    #[\Override]
    public function setLogger(LoggerInterface $logger)
    {
        if ($this->deferredScheduler instanceof LoggerAwareInterface) {
            $this->deferredScheduler->setLogger($logger);
        }
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->deferredScheduler->flush();
    }
}
