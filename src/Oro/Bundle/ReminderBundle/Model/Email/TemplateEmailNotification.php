<?php

namespace Oro\Bundle\ReminderBundle\Model\Email;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\EmailBundle\Model\SenderAwareInterface;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\NotificationBundle\Model\TemplateEmailNotificationInterface;
use Oro\Bundle\ReminderBundle\Entity\Reminder;
use Oro\Bundle\ReminderBundle\Exception\InvalidArgumentException;

/**
 * Notification model for a reminder.
 */
class TemplateEmailNotification implements SenderAwareInterface, TemplateEmailNotificationInterface
{
    const CONFIG_FIELD = 'reminder_template_name';

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Reminder
     */
    private $reminder;

    /**
     * @var EntityNameResolver
     */
    private $entityNameResolver;

    public function __construct(
        ObjectManager $em,
        ConfigProvider $configProvider,
        EntityNameResolver $entityNameResolver
    ) {
        $this->em = $em;
        $this->configProvider = $configProvider;
        $this->entityNameResolver = $entityNameResolver;
    }

    public function setReminder(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function getEntity()
    {
        return $this->em
            ->getRepository($this->getReminder()->getRelatedEntityClassName())
            ->find($this->getReminder()->getRelatedEntityId());
    }

    #[\Override]
    public function getSender(): ?From
    {
        $sender = $this->getReminder()->getSender();

        return $sender
            ? From::emailAddress($sender->getEmail(), $this->entityNameResolver->getName($sender))
            : null;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getReminder(): Reminder
    {
        if (!$this->reminder) {
            throw new InvalidArgumentException('Reminder was not set');
        }

        return $this->reminder;
    }

    /**
     * @throws InvalidArgumentException|RuntimeException
     */
    #[\Override]
    public function getTemplateCriteria(): EmailTemplateCriteria
    {
        $className = $this->getReminder()->getRelatedEntityClassName();
        $templateName = $this->configProvider->getConfig($className)->get(self::CONFIG_FIELD, true);

        return new EmailTemplateCriteria($templateName, $className);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function getRecipients(): iterable
    {
        return [$this->getReminder()->getRecipient()];
    }
}
