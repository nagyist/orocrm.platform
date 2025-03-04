<?php

namespace Oro\Bundle\ReportBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateReportJsonArrayQuery extends ParametrizedMigrationQuery
{
    #[\Override]
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Convert a column with "json_array(text)" type to "json_array" type on PostgreSQL >= 9.2 and Doctrine 2.5'
        );
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof PostgreSQL92Platform) {
            $updateSql = 'ALTER TABLE oro_report ALTER COLUMN chart_options TYPE JSON USING chart_options::JSON';

            $this->logQuery($logger, $updateSql);
            if (!$dryRun) {
                $this->connection->executeStatement($updateSql);
            }
        }
    }
}
