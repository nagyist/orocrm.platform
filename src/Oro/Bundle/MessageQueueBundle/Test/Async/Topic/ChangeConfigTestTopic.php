<?php

namespace Oro\Bundle\MessageQueueBundle\Test\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A test topic to change config
 */
class ChangeConfigTestTopic extends AbstractTopic
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro.test.change_config';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Test topic to change config.';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('message')
            ->setAllowedTypes('message', 'string');
    }
}
