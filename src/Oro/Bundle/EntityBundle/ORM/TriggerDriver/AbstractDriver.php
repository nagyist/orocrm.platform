<?php

namespace Oro\Bundle\EntityBundle\ORM\TriggerDriver;

use Oro\Bundle\EntityBundle\Manager\Db\EntityTriggerDriverInterface;
use Oro\Bundle\EntityBundle\ORM\DatabaseDriverInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Abstract driver for different  PDO (MySQL/POSTGRESQL)
 */
abstract class AbstractDriver implements DatabaseDriverInterface, EntityTriggerDriverInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tableName;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }
    #[\Override]
    public function disable()
    {
        $this->init();
        $this->connection->executeStatement(sprintf($this->getSqlDisable(), $this->tableName));
    }

    #[\Override]
    public function enable()
    {
        $this->init();
        $this->connection->executeStatement(sprintf($this->getSqlEnable(), $this->tableName));
    }

    #[\Override]
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return string
     */
    abstract protected function getSqlDisable();

    /**
     * @return string
     */
    abstract protected function getSqlEnable();

    private function init()
    {
        $this->connection = $this->doctrineHelper->getEntityManagerForClass($this->entityClass)
            ->getConnection();

        $this->tableName = $this->doctrineHelper->getEntityManagerForClass($this->entityClass)
            ->getClassMetadata($this->entityClass)
            ->getTableName();
    }
}
