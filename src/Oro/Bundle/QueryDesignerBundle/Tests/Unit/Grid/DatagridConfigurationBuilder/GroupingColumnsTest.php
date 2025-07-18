<?php

namespace Oro\Bundle\QueryDesignerBundle\Tests\Unit\Grid\DatagridConfigurationBuilder;

use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\QueryDesignerBundle\Model\QueryDesigner;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\QueryDefinitionUtil;

class GroupingColumnsTest extends DatagridConfigurationBuilderTestCase
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGrouping(): void
    {
        $en = 'Acme\Entity\TestEntity';
        $definition = [
            'columns'          => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => 'DESC'],
                [
                    'name'    => 'column2',
                    'label'   => 'lbl2',
                    'sorting' => '',
                    'func'    => [
                        'name'       => 'Count',
                        'group_name' => 'string',
                        'group_type' => 'aggregates'
                    ]
                ],
                [
                    'name'    => 'column3',
                    'label'   => 'lbl3',
                    'sorting' => '',
                    'func'    => [
                        'name'       => 'Year',
                        'group_name' => 'date',
                        'group_type' => 'converters',
                        'return_type' => 'number',
                    ]
                ]
            ],
            'filters'          => [],
            'grouping_columns' => [['name' => 'column1']],
        ];
        $doctrine = $this->getDoctrine(
            [
                $en => ['column1' => 'string', 'column2' => 'string', 'column3' => 'date'],
            ]
        );
        $functionProvider = $this->getFunctionProvider(
            [
                [
                    'Count',
                    'string',
                    'aggregates',
                    ['name' => 'Count', 'return_type' => 'integer', 'expr' => 'COUNT($column)']
                ],
                [
                    'Year',
                    'date',
                    'converters',
                    ['name' => 'Year', 'return_type' => 'number', 'expr' => 'YEAR($column)']
                ]
            ]
        );

        $model = new QueryDesigner($en, QueryDefinitionUtil::encodeDefinition($definition));
        $builder = $this->createDatagridConfigurationBuilder($model, $doctrine, $functionProvider);
        $result = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select'  => [
                        't1.column1 as c1',
                        'COUNT(t1.column2) as c2',
                        'YEAR(t1.column3) as c3'
                    ],
                    'from'    => [
                        ['table' => $en, 'alias' => 't1']
                    ],
                    'groupBy' => 'c1'
                ],
                'query_config' => [
                    'table_aliases'  => [
                        '' => 't1'
                    ],
                    'column_aliases' => [
                        'column1'                          => 'c1',
                        'column2(Count,string,aggregates)' => 'c2',
                        'column3(Year,date,converters)'    => 'c3',
                    ],
                ]
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string', 'translatable' => false],
                'c2' => ['label' => 'lbl2', 'frontend_type' => 'integer', 'translatable' => false],
                'c3' => ['label' => 'lbl3', 'frontend_type' => 'number', 'translatable' => false],
            ],
            'name'    => 'test_grid',
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 'c1'],
                    'c2' => ['data_name' => 'c2'],
                    'c3' => ['data_name' => 'c3']
                ],
                'default' => ['c1' => 'DESC']
            ],
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 'c1', 'type' => 'string', 'translatable' => false],
                    'c2' => [
                        'data_name'                  => 'c2',
                        'type'                       => 'number',
                        'translatable'               => false,
                        FilterUtility::BY_HAVING_KEY => true
                    ],
                    'c3' => [
                        'data_name'                  => 'c3',
                        'type'                       => 'number',
                        'translatable'               => false,
                        FilterUtility::BY_HAVING_KEY => true,
                    ]
                ]
            ],
            'fields_acl' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1'],
                    'c2' => ['data_name' => 't1.column2'],
                    'c3' => ['data_name' => 't1.column3'],
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testWithoutGrouping(): void
    {
        $en = 'Acme\Entity\TestEntity';
        $definition = [
            'columns'          => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => 'DESC'],
                [
                    'name'    => 'column2',
                    'label'   => 'lbl2',
                    'sorting' => '',
                    'func'    => [
                        'name'       => 'Count',
                        'group_name' => 'string',
                        'group_type' => 'aggregates'
                    ]
                ],
                [
                    'name'    => 'column3',
                    'label'   => 'lbl3',
                    'sorting' => '',
                    'func'    => [
                        'name'       => 'Year',
                        'group_name' => 'date',
                        'group_type' => 'converters',
                        'return_type' => 'number',
                    ]
                ]
            ],
            'filters'          => [],
        ];
        $doctrine = $this->getDoctrine(
            [
                $en => ['column1' => 'string', 'column2' => 'string', 'column3' => 'date'],
            ]
        );
        $functionProvider = $this->getFunctionProvider(
            [
                [
                    'Count',
                    'string',
                    'aggregates',
                    ['name' => 'Count', 'return_type' => 'integer', 'expr' => 'COUNT($column)']
                ],
                [
                    'Year',
                    'date',
                    'converters',
                    ['name' => 'Year', 'return_type' => 'number', 'expr' => 'YEAR($column)']
                ]
            ]
        );

        $model = new QueryDesigner($en, QueryDefinitionUtil::encodeDefinition($definition));
        $builder = $this->createDatagridConfigurationBuilder($model, $doctrine, $functionProvider);
        $result = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select'  => [
                        't1.column1 as c1',
                        'COUNT(t1.column2) as c2',
                        'YEAR(t1.column3) as c3'
                    ],
                    'from'    => [
                        ['table' => $en, 'alias' => 't1']
                    ],
                ],
                'query_config' => [
                    'table_aliases'  => [
                        '' => 't1'
                    ],
                    'column_aliases' => [
                        'column1'                          => 'c1',
                        'column2(Count,string,aggregates)' => 'c2',
                        'column3(Year,date,converters)'    => 'c3',
                    ],
                ]
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string', 'translatable' => false],
                'c2' => ['label' => 'lbl2', 'frontend_type' => 'integer', 'translatable' => false],
                'c3' => ['label' => 'lbl3', 'frontend_type' => 'number', 'translatable' => false],
            ],
            'name'    => 'test_grid',
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 'c1'],
                    'c2' => ['data_name' => 'c2'],
                    'c3' => ['data_name' => 'c3']
                ],
                'default' => ['c1' => 'DESC']
            ],
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 'c1', 'type' => 'string', 'translatable' => false],
                    'c2' => [
                        'data_name'                  => 'c2',
                        'type'                       => 'number',
                        'translatable'               => false,
                    ],
                    'c3' => [
                        'data_name'                  => 'c3',
                        'type'                       => 'number',
                        'translatable'               => false,
                    ]
                ]
            ],
            'fields_acl' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1'],
                    'c2' => ['data_name' => 't1.column2'],
                    'c3' => ['data_name' => 't1.column3'],
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalidGrouping(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The grouping column "column2" must be declared in SELECT clause.');

        $en = 'Acme\Entity\TestEntity';
        $definition = [
            'columns'          => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => 'DESC'],
                [
                    'name'    => 'column2',
                    'label'   => 'lbl2',
                    'sorting' => '',
                    'func'    => [
                        'name'       => 'Count',
                        'group_name' => 'string',
                        'group_type' => 'aggregates'
                    ]
                ]
            ],
            'filters'          => [],
            'grouping_columns' => [['name' => 'column2']],
        ];
        $doctrine = $this->getDoctrine(
            [
                $en => ['column1' => 'string'],
                $en => ['column2' => 'string']
            ]
        );
        $functionProvider = $this->getFunctionProvider(
            [
                [
                    'Count',
                    'string',
                    'aggregates',
                    ['name' => 'Count', 'return_type' => 'integer', 'expr' => 'COUNT($column)']
                ]
            ]
        );

        $model = new QueryDesigner($en, QueryDefinitionUtil::encodeDefinition($definition));
        $this->createDatagridConfigurationBuilder($model, $doctrine, $functionProvider)
            ->getConfiguration();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testComplexQuery(): void
    {
        $en = 'Acme\Entity\TestEntity';
        $en1 = 'Acme\Entity\TestEntity1';
        $en2 = 'Acme\Entity\TestEntity2';
        $en3 = 'Acme\Entity\TestEntity3';
        $definition = [
            'columns' => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => 'DESC'],
                ['name' => 'rc1+' . $en1 . '::column2', 'label' => 'lbl2', 'sorting' => ''],
                ['name' => 'rc2+' . $en2 . '::column3', 'label' => 'lbl3', 'sorting' => 'ASC'],
            ],
            'filters' => [
                [
                    'columnName' => 'rc1+' . $en1 . '::column2',
                    'criterion'  => [
                        'filter' => 'string',
                        'data'   => [
                            'type'  => '1',
                            'value' => 'test'
                        ]
                    ]
                ],
                'OR',
                [
                    [
                        'columnName' => 'rc1+' . $en1 . '::rc4+' . $en3 . '::column5',
                        'criterion'  => [
                            'filter' => 'string',
                            'data'   => [
                                'type'  => '1',
                                'value' => 'test'
                            ]
                        ]
                    ],
                    'and',
                    [
                        'columnName' => 'rc1+' . $en1 . '::rc4+' . $en3 . '::column6',
                        'criterion'  => [
                            'filter' => 'string',
                            'data'   => [
                                'type'  => '1',
                                'value' => 'test'
                            ]
                        ]
                    ],
                ],
            ],
        ];
        $doctrine = $this->getDoctrine(
            [
                $en  => [
                    'column1' => 'string',
                    'rc1'     => ['nullable' => true],
                    'rc2'     => ['nullable' => true]
                ],
                $en1 => [
                    'column2' => 'integer',
                    'rc4'     => ['nullable' => true]
                ],
                $en2 => ['column3' => 'float'],
                $en3 => ['column5' => 'string'],
            ]
        );

        $model = new QueryDesigner($en, QueryDefinitionUtil::encodeDefinition($definition));
        $builder = $this->createDatagridConfigurationBuilder($model, $doctrine);
        $result = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select' => [
                        't1.column1 as c1',
                        't2.column2 as c2',
                        't4.column3 as c3',
                    ],
                    'from'   => [
                        ['table' => $en, 'alias' => 't1']
                    ],
                    'join'   => [
                        'left' => [
                            ['join' => 't1.rc1', 'alias' => 't2'],
                            ['join' => 't2.rc4', 'alias' => 't3'],
                            ['join' => 't1.rc2', 'alias' => 't4'],
                        ]
                    ]
                ],
                'query_config' => [
                    'table_aliases'  => [
                        ''                              => 't1',
                        $en . '::rc1'                   => 't2',
                        $en . '::rc1+' . $en1 . '::rc4' => 't3',
                        $en . '::rc2'                   => 't4',
                    ],
                    'column_aliases' => [
                        'column1'                   => 'c1',
                        'rc1+' . $en1 . '::column2' => 'c2',
                        'rc2+' . $en2 . '::column3' => 'c3',
                    ],
                    'filters'        => [
                        [
                            'column'      => 't2.column2',
                            'filter'      => 'string',
                            'filterData'  => [
                                'type'  => '1',
                                'value' => 'test'
                            ],
                            'columnAlias' => 'c2'
                        ],
                        'OR',
                        [
                            [
                                'column'     => 't3.column5',
                                'filter'     => 'string',
                                'filterData' => [
                                    'type'  => '1',
                                    'value' => 'test'
                                ]
                            ],
                            'AND',
                            [
                                'column'     => 't3.column6',
                                'filter'     => 'string',
                                'filterData' => [
                                    'type'  => '1',
                                    'value' => 'test'
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string', 'translatable' => false],
                'c2' => ['label' => 'lbl2', 'frontend_type' => 'integer', 'translatable' => false],
                'c3' => ['label' => 'lbl3', 'frontend_type' => 'decimal', 'translatable' => false],
            ],
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 'c1'],
                    'c2' => ['data_name' => 'c2'],
                    'c3' => ['data_name' => 'c3']
                ],
                'default' => [
                    'c1' => 'DESC',
                    'c3' => 'ASC',
                ]
            ],
            'name'    => 'test_grid',
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 'c1', 'type' => 'string', 'translatable' => false],
                    'c2' => ['data_name' => 'c2', 'type' => 'number', 'translatable' => false],
                    'c3' => ['data_name' => 'c3', 'type' => 'number', 'translatable' => false]
                ]
            ],
            'fields_acl' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1'],
                    'c2' => ['data_name' => 't2.column2'],
                    'c3' => ['data_name' => 't4.column3']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
