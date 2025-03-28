<?php

namespace Oro\Bundle\EntityConfigBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateIndexedConfigValues implements Migration, OrderedMigrationInterface, RenameExtensionAwareInterface
{
    use RenameExtensionAwareTrait;

    #[\Override]
    public function getOrder()
    {
        return 2;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery($this->getRemoveObsoleteValuesSql());

        $table = $schema->getTable('oro_entity_config_value');

        $table->dropColumn('serializable');

        $table->getColumn('value')
            ->setType(Type::getType(Types::STRING));

        $table->addIndex(
            ['scope', 'code', 'value', 'entity_id'],
            'idx_entity_config_index_entity'
        );
        $table->addIndex(
            ['scope', 'code', 'value', 'field_id'],
            'idx_entity_config_index_field'
        );

        $this->renameExtension->renameTable(
            $schema,
            $queries,
            'oro_entity_config_value',
            'oro_entity_config_index_value'
        );

        $queries->addPostQuery(new UpdateModuleAndEntityFieldsQuery());
    }

    /**
     * @return string
     */
    protected function getRemoveObsoleteValuesSql()
    {
        return 'DELETE FROM oro_entity_config_value WHERE NOT ('
        . " (scope = 'dataaudit' AND code = 'auditable')" // for both entity and field
        . " OR (scope = 'entity' AND code IN ('label'))" // for both entity and field
        . " OR (scope = 'extend' AND code IN ('owner', 'state', 'is_deleted'))" // for both entity and field
        . " OR (scope = 'extend' AND code = 'is_extend' AND field_id IS NULL)" // for entity only
        . " OR (scope = 'ownership' AND code = 'owner_type' AND field_id IS NULL)" // for entity only
        . ')';
    }
}
