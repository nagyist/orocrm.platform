<?php

namespace Oro\Bundle\QueryDesignerBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\SegmentFiltersPurifier;
use PHPUnit\Framework\TestCase;

class SegmentFiltersPurifierTest extends TestCase
{
    private SegmentFiltersPurifier $segmentFiltersPurifier;

    #[\Override]
    protected function setUp(): void
    {
        $this->segmentFiltersPurifier = new SegmentFiltersPurifier();
    }

    /**
     * @dataProvider purifyFiltersDataProvider
     */
    public function testPurifyFilters(array $filters, array $expectedFilters): void
    {
        self::assertEquals($expectedFilters, $this->segmentFiltersPurifier->purifyFilters($filters));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function purifyFiltersDataProvider(): array
    {
        return [
            'empty tokens (filter or group) are skipped along with operator' => [
                'filters' => [
                    [],
                    'OR',
                    []
                ],
                'expectedFilters' => []
            ],
            'first empty token (filter or group) is skipped along with operator' => [
                'filters' => [
                    [],
                    'OR',
                    [
                        'columnName' => 'sku',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => ['value' => '7Y', 'type' => 4]
                        ]
                    ]
                ],
                'expectedFilters' => [
                    [
                        'columnName' => 'sku',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => ['value' => '7Y', 'type' => 4]
                        ]
                    ]
                ]
            ],
            'second empty token (filter or group) is skipped along with operator' => [
                'filters' => [
                    [
                        'columnName' => 'sku',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => ['value' => '7Y', 'type' => 4]
                        ]
                    ],
                    'OR',
                    []
                ],
                'expectedFilters' => [
                    [
                        'columnName' => 'sku',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => ['value' => '7Y', 'type' => 4]
                        ]
                    ]
                ]
            ],
            'token in the middle (filter or group) is skipped, last operator is used' => [
                'filters' => [
                    [
                        'columnName' => 'sku',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => ['value' => '7Y', 'type' => 4]
                        ]
                    ],
                    'OR',
                    [],
                    'AND',
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ],
                'expectedFilters' => [
                    [
                        'columnName' => 'sku',
                        'criterion' => [
                            'filter' => 'string',
                            'data' => ['value' => '7Y', 'type' => 4]
                        ]
                    ],
                    'AND',
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ]
            ],
            'group with only one empty filter is skipped along with operator' => [
                'filters' => [
                    [
                        []
                    ],
                    'AND',
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ],
                'expectedFilters' => [
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ]
            ],
            'group with two empty filters is skipped along with operator' => [
                'filters' => [
                    [
                        [],
                        'AND',
                        []
                    ],
                    'AND',
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ],
                'expectedFilters' => [
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ]
            ],
            'empty filter is skipped from a group along with operator' => [
                'filters' => [
                    [
                        [],
                        'OR',
                        [
                            'columnName' => 'status',
                            'criterion' => [
                                'filter' => 'string',
                                'data' => ['value' => '', 'type' => 'filter_empty_option']
                            ]
                        ]
                    ],
                    'AND',
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ],
                'expectedFilters' => [
                    [
                        [
                            'columnName' => 'status',
                            'criterion' => [
                                'filter' => 'string',
                                'data' => ['value' => '', 'type' => 'filter_empty_option']
                            ]
                        ]
                    ],
                    'AND',
                    [
                        'columnName' => 'id',
                        'criterion' => [
                            'filter' => 'number',
                            'data' => ['value' => 1, 'type' => 3]
                        ]
                    ]
                ]
            ],
        ];
    }
}
