<?php

namespace Oro\Bundle\TranslationBundle\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Remove unused translation keys.
 */
class DeleteTranslationKeysQuery extends ParametrizedMigrationQuery
{
    /** @var string */
    private $domain;

    /** @var array */
    private $translationKeys;

    /**
     * @param string $domain
     * @param array $translationKeys
     */
    public function __construct($domain, array $translationKeys)
    {
        $this->domain = $domain;
        $this->translationKeys = $translationKeys;
    }

    #[\Override]
    public function getDescription()
    {
        return 'Remove unused translation keys';
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        $this->deleteTranslationKeys($logger, $this->domain, $this->translationKeys);
    }

    /**
     * @param LoggerInterface $logger
     * @param string $domain
     * @param array $translationKeys
     */
    protected function deleteTranslationKeys(LoggerInterface $logger, $domain, array $translationKeys)
    {
        // Delete unused translation keys.
        $params = [
            'translation_keys' => $translationKeys,
            'domain' => $domain
        ];

        $keyField = $this->connection->getDatabasePlatform() instanceof MySqlPlatform ? '`key`' : 'key';

        $types  = [
            'translation_keys' => Connection::PARAM_STR_ARRAY,
            'domain' => Types::STRING,
        ];

        $sql = sprintf(
            'DELETE FROM oro_translation_key WHERE %s IN (:translation_keys) AND domain = :domain',
            $keyField
        );

        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeStatement($sql, $params, $types);
    }
}
