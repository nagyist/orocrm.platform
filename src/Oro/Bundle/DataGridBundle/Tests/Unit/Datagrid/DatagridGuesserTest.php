<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\ColumnOptionsGuesserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridGuesser;
use Oro\Bundle\DataGridBundle\Datagrid\Guess\ColumnGuess;
use PHPUnit\Framework\TestCase;

class DatagridGuesserTest extends TestCase
{
    public function testApplyColumnGuesses(): void
    {
        $class = 'TestClass';
        $property = 'testProp';
        $type = 'integer';

        $guesser1 = $this->createMock(ColumnOptionsGuesserInterface::class);
        $guesser2 = $this->createMock(ColumnOptionsGuesserInterface::class);

        $formatterGuess = new ColumnGuess(
            ['formatter_prop1' => 'prop1', 'formatter_prop2' => 'prop2'],
            ColumnGuess::LOW_CONFIDENCE
        );
        $sorterGuess = new ColumnGuess(
            ['sorter_prop1' => 'prop1', 'sorter_prop2' => 'prop2'],
            ColumnGuess::LOW_CONFIDENCE
        );
        $filterGuess = new ColumnGuess(
            ['filter_prop1' => 'prop1', 'filter_prop2' => 'prop2'],
            ColumnGuess::LOW_CONFIDENCE
        );

        $guesser1->expects($this->once())
            ->method('guessFormatter')
            ->with($class, $property, $type)
            ->willReturn(null);
        $guesser2->expects($this->once())
            ->method('guessFormatter')
            ->with($class, $property, $type)
            ->willReturn($formatterGuess);

        $guesser1->expects($this->once())
            ->method('guessSorter')
            ->with($class, $property, $type)
            ->willReturn(null);
        $guesser2->expects($this->once())
            ->method('guessSorter')
            ->with($class, $property, $type)
            ->willReturn($sorterGuess);

        $guesser1->expects($this->once())
            ->method('guessFilter')
            ->with($class, $property, $type)
            ->willReturn(null);
        $guesser2->expects($this->once())
            ->method('guessFilter')
            ->with($class, $property, $type)
            ->willReturn($filterGuess);

        $datagridGuesser = new DatagridGuesser([$guesser1, $guesser2]);
        $columnOptions = [
            DatagridGuesser::FORMATTER => [
                'formatter_prop1' => 'prop1_initial',
                'formatter_prop3' => 'prop3_initial',
            ],
            DatagridGuesser::SORTER    => [
                'sorter_prop1' => 'prop1_initial',
                'sorter_prop3' => 'prop1_initial',
            ],
            DatagridGuesser::FILTER    => [
                'filter_prop1' => 'prop1_initial',
                'filter_prop3' => 'prop3_initial',
            ],
        ];
        $datagridGuesser->applyColumnGuesses($class, $property, $type, $columnOptions);

        $this->assertEquals(
            [
                DatagridGuesser::FORMATTER => [
                    'formatter_prop1' => 'prop1_initial',
                    'formatter_prop2' => 'prop2',
                    'formatter_prop3' => 'prop3_initial',
                ],
                DatagridGuesser::SORTER    => [
                    'sorter_prop1' => 'prop1_initial',
                    'sorter_prop2' => 'prop2',
                    'sorter_prop3' => 'prop1_initial',
                ],
                DatagridGuesser::FILTER    => [
                    'filter_prop1' => 'prop1_initial',
                    'filter_prop2' => 'prop2',
                    'filter_prop3' => 'prop3_initial',
                ],
            ],
            $columnOptions
        );
    }

    public function testSetColumnOptions(): void
    {
        $datagridGuesser = new DatagridGuesser([]);

        $config = DatagridConfiguration::create([]);

        $columnOptions = [
            DatagridGuesser::FORMATTER => [
                'formatter_prop' => 'test'
            ],
            DatagridGuesser::SORTER    => [
                'sorter_prop' => 'test'
            ],
            DatagridGuesser::FILTER    => [
                'filter_prop' => 'test'
            ],
        ];

        $datagridGuesser->setColumnOptions($config, 'testColumn', $columnOptions);

        $this->assertEquals(
            [
                'columns' => [
                    'testColumn' => [
                        'formatter_prop' => 'test'
                    ]
                ],
                'sorters' => [
                    'columns' => [
                        'testColumn' => [
                            'sorter_prop' => 'test'
                        ]
                    ]
                ],
                'filters' => [
                    'columns' => [
                        'testColumn' => [
                            'filter_prop' => 'test'
                        ]
                    ]
                ],
            ],
            $config->toArray()
        );
    }
}
