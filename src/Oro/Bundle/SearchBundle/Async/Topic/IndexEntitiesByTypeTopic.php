<?php

namespace Oro\Bundle\SearchBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Index entities by class name.
 */
class IndexEntitiesByTypeTopic extends AbstractTopic
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro.search.index_entity_type';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Index entities by class name';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['jobId', 'entityClass'])
            ->addAllowedTypes('jobId', 'int')
            ->addAllowedTypes('entityClass', 'string');
    }
}
