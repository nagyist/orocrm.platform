<?php

namespace Oro\Component\Layout\Tests\Unit\Block\Type;

use Oro\Component\Layout\Block\Type\BaseType;
use Oro\Component\Layout\Tests\Unit\BaseBlockTypeTestCase;

class BaseTypeTest extends BaseBlockTypeTestCase
{
    public function testConfigureOptionsWithEmptyOptions(): void
    {
        $this->assertEquals(
            ['visible' => true],
            $this->resolveOptions(BaseType::NAME, [])
        );
    }

    public function testConfigureOptionsWithValidOptions(): void
    {
        $this->assertEquals(
            [
                'visible'            => true,
                'vars'               => ['test_var' => 'test_var_val'],
                'attr'               => ['test_attr' => 'test_attr_val'],
                'label'              => 'Test Label',
                'label_attr'         => ['test_label_attr' => 'test_label_attr_val'],
                'translation_domain' => 'test_translation_domain'
            ],
            $this->resolveOptions(
                BaseType::NAME,
                [
                    'vars'               => ['test_var' => 'test_var_val'],
                    'attr'               => ['test_attr' => 'test_attr_val'],
                    'label'              => 'Test Label',
                    'label_attr'         => ['test_label_attr' => 'test_label_attr_val'],
                    'translation_domain' => 'test_translation_domain'
                ]
            )
        );
    }

    public function testBuildViewWithoutOptions(): void
    {
        $view = $this->getBlockBuilder(BaseType::NAME, [], 'test:block--1')
            ->getBlockView();

        $this->assertSame($view, $view->vars['block']);
        unset($view->vars['block']);

        $this->assertBlockView(
            [
                'vars' => [
                    'id'                   => 'test:block--1',
                    'block_type'           => 'block',
                    'block_type_widget_id' => 'block_widget',
                    'unique_block_prefix'  => '_test_block_1',
                    'block_prefixes'       => [
                        'block',
                        '_test_block_1'
                    ],
                    'cache_key'            => '_test:block--1_block_ad7b81dea42cf2ef7525c274471e3ce6',
                    'translation_domain'   => 'messages',
                    'visible'              => true,
                    '_blockThemes'         => [],
                    '_formThemes'          => [],
                ]
            ],
            $view,
            false
        );
    }

    public function testBuildView(): void
    {
        $options = [
            'vars'               => ['test_var' => 'test_var_val'],
            'attr'               => ['test_attr' => 'test_attr_val'],
            'label'              => 'Test Label',
            'label_attr'         => ['test_label_attr' => 'test_label_attr_val'],
            'translation_domain' => 'test_translation_domain',
            'additional_block_prefixes' => ['additional_prefix_1', 'additional_prefix_2']
        ];

        $view = $this->getBlockBuilder(BaseType::NAME, $options)
            ->getBlockView();

        $this->assertSame($view, $view->vars['block']);
        unset($view->vars['block']);

        $this->assertBlockView(
            [
                'vars' => [
                    'id'                   => 'block_id',
                    'block_type'           => 'block',
                    'block_type_widget_id' => 'block_widget',
                    'unique_block_prefix'  => '_block_id',
                    'block_prefixes'       => [
                        'block',
                        'additional_prefix_1',
                        'additional_prefix_2',
                        '_block_id'
                    ],
                    'cache_key'            => '_block_id_block_ad7b81dea42cf2ef7525c274471e3ce6',
                    'translation_domain'   => 'test_translation_domain',
                    'attr'                 => ['test_attr' => 'test_attr_val'],
                    'label'                => 'Test Label',
                    'label_attr'           => ['test_label_attr' => 'test_label_attr_val'],
                    'test_var'             => 'test_var_val',
                    '_blockThemes'         => [],
                    '_formThemes'          => [],
                ]
            ],
            $view,
            false
        );
    }
}
