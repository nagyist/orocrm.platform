<?php

namespace Oro\Bundle\MigrationBundle\Migration\Schema;

use Doctrine\DBAL\Schema\Column as BaseColumn;
use Doctrine\DBAL\Schema\Table as BaseTable;

/**
 * The aim of this class is to provide a way extend doctrine Column class
 * To do this just define your column class name in COLUMN_CLASS constant in an extended class
 * and override createColumnObject if your column class constructor need an additional arguments
 */
class Table extends BaseTable
{
    /**
     * Used column class, define COLUMN_CLASS constant in an extended class to extend the column class
     * Important: your class must extend Oro\Bundle\MigrationBundle\Migration\Schema\Column class
     *            or extend Doctrine\DBAL\Schema\Column class and must have __construct(array $args) method
     */
    const COLUMN_CLASS = 'Doctrine\DBAL\Schema\Column';

    /**
     * Creates an instance of COLUMN_CLASS class
     *
     * @param array $args An arguments for COLUMN_CLASS class constructor
     *                    An instance of a base column is in 'column' element
     * @return BaseColumn
     */
    protected function createColumnObject(array $args)
    {
        $columnClass = static::COLUMN_CLASS;

        return new $columnClass($args);
    }

    /**
     * Constructor
     */
    public function __construct(array $args)
    {
        /** @var BaseTable $baseTable */
        $baseTable = $args['table'];

        parent::__construct(
            $baseTable->getName(),
            $baseTable->getColumns(),
            $baseTable->getIndexes(),
            $baseTable->getForeignKeys(),
            false,
            $baseTable->getOptions()
        );
    }

    #[\Override]
    public function addColumn($columnName, $typeName, array $options = [])
    {
        parent::addColumn($columnName, $typeName, $options);

        return $this->getColumn($columnName);
    }

    // @codingStandardsIgnoreStart
    #[\Override]
    protected function _addColumn(BaseColumn $column)
    {
        if (get_class($column) !== static::COLUMN_CLASS && static::COLUMN_CLASS !== 'Doctrine\DBAL\Schema\Column') {
            $column = $this->createColumnObject(['column' => $column]);
        }
        parent::_addColumn($column);
    }
    // @codingStandardsIgnoreEnd
}
