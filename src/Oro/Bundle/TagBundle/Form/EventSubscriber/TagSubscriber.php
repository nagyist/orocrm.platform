<?php

namespace Oro\Bundle\TagBundle\Form\EventSubscriber;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\TagBundle\Helper\TaggableHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Loads tagging and assign to entity on pre set
 */
class TagSubscriber implements EventSubscriberInterface
{
    /** @var TagManager */
    protected $tagManager;

    /** @var TaggableHelper */
    protected $taggableHelper;

    /** @var Organization|null */
    protected $organization;

    public function __construct(TagManager $tagManager, TaggableHelper $taggableHelper)
    {
        $this->tagManager     = $tagManager;
        $this->taggableHelper = $taggableHelper;
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet'
        ];
    }

    public function preSet(FormEvent $event)
    {
        $entity = $event->getForm()->getParent()->getData();

        if (!$this->taggableHelper->isTaggable($entity)) {
            return;
        }

        $this->tagManager->loadTagging($entity);
        $tags = $this->tagManager->getPreparedArray($entity, null, $this->organization);

        $event->setData($tags);
    }
}
