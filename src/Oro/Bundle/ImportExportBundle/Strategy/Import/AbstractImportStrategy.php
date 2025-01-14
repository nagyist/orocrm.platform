<?php

namespace Oro\Bundle\ImportExportBundle\Strategy\Import;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ImportExportBundle\Processor\EntityNameAwareInterface;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract class for the import strategy implementations.
 */
abstract class AbstractImportStrategy implements StrategyInterface, ContextAwareInterface, EntityNameAwareInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var DatabaseHelper
     */
    protected $databaseHelper;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var ContextInterface
     */
    protected $context;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ImportStrategyHelper $strategyHelper,
        FieldHelper $fieldHelper,
        DatabaseHelper $databaseHelper
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->strategyHelper = $strategyHelper;
        $this->fieldHelper = $fieldHelper;
        $this->databaseHelper = $databaseHelper;
    }

    #[\Override]
    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    #[\Override]
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param object $entity
     * @return object
     */
    protected function beforeProcessEntity($entity)
    {
        $event = new StrategyEvent($this, $entity, $this->context);
        $this->eventDispatcher->dispatch($event, StrategyEvent::PROCESS_BEFORE);
        return $event->getEntity();
    }

    /**
     * @param object $entity
     * @return object
     */
    protected function afterProcessEntity($entity)
    {
        $event = new StrategyEvent($this, $entity, $this->context);
        $this->eventDispatcher->dispatch($event, StrategyEvent::PROCESS_AFTER);
        return $event->getEntity();
    }

    /**
     * @param object $entity
     * @param array $searchContext
     * @return null|object
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $entityName = ClassUtils::getClass($entity);
        $identifier = $this->databaseHelper->getIdentifier($entity);
        $existingEntity = null;

        // find by identifier
        if ($identifier) {
            $existingEntity = $this->databaseHelper->find($entityName, $identifier);
        }

        // find by identity fields
        if (!$existingEntity
            && (!$searchContext || $this->databaseHelper->getIdentifier(current($searchContext)))
        ) {
            $existingEntity = $this->findExistingEntityByIdentityFields($entity, $searchContext);
        }

        if ($existingEntity && !$identifier) {
            $identifier = $this->databaseHelper->getIdentifier($existingEntity);
            $identifierName = $this->databaseHelper->getIdentifierFieldName($entity);
            $this->fieldHelper->setObjectValue($entity, $identifierName, $identifier);
        }

        return $existingEntity;
    }

    /**
     * @param object $entity
     * @param array $searchContext
     *
     * @return object|null
     */
    protected function findExistingEntityByIdentityFields($entity, array $searchContext = [])
    {
        $entityName = ClassUtils::getClass($entity);

        $identityValues = $searchContext;
        $identityValues += $this->fieldHelper->getIdentityValues($entity);
        foreach ($identityValues as $fieldName => &$value) {
            if (is_object($value)) {
                $value = $this->findExistingEntity($value);
            }

            if ($value !== null || $this->fieldHelper->isRequiredIdentityField($entityName, $fieldName)) {
                continue;
            }

            unset($identityValues[$fieldName]);
        }
        unset($value);

        return $this->findEntityByIdentityValues($entityName, $identityValues);
    }

    /**
     * Try to find entity by identity fields if at least one is specified
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        foreach ($identityValues as $value) {
            if (null !== $value && '' !== $value) {
                return $this->databaseHelper->findOneBy($entityName, $identityValues);
            }
        }

        return null;
    }

    /**
     * @param object $entity
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    protected function assertEnvironment($entity)
    {
        if (!$this->context) {
            throw new LogicException('Strategy must have import/export context');
        }

        if (!$this->entityName) {
            throw new LogicException('Strategy must know about entity name');
        }

        $entityName = $this->entityName;
        if (!$entity instanceof $entityName) {
            throw new InvalidArgumentException(sprintf('Imported entity must be instance of %s', $entityName));
        }
    }
}
