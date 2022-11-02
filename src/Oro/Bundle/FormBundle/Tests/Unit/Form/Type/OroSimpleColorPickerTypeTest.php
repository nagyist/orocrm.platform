<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Form\Type\OroSimpleColorPickerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Form\Test\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class OroSimpleColorPickerTypeTest extends FormIntegrationTestCase
{
    /** @var OroSimpleColorPickerType */
    private $formType;

    protected function setUp(): void
    {
        parent::setUp();

        $configManager = $this->createMock(ConfigManager::class);
        $translatorInterface = $this->createMock(TranslatorInterface::class);

        $this->formType = new OroSimpleColorPickerType($configManager, $translatorInterface);
    }

    public function testConfigureOptionsWithCustomColorSchema()
    {
        $resolver = $this->getOptionsResolver();
        $this->formType->configureOptions($resolver);

        $options = [
            'color_schema'  => 'custom',
            'colors'        => [
                '#FFFFFF',
                '#000000',
            ],
        ];

        $resolvedOptions = $resolver->resolve($options);

        $this->assertEquals(
            [
                'colors'                => [
                    '#FFFFFF',
                    '#000000',
                ],
                'translatable'          => false,
                'allow_empty_color'     => false,
                'empty_color'           => null,
                'picker'                => false,
                'picker_delay'          => 0,
                'color_schema'          => 'custom',
                'empty_value'           => null,
                'allow_custom_color'    => false,
                'custom_color_control'  => null,

            ],
            $resolvedOptions
        );
    }

    public function testConfigureOptionsWithShortColorSchema()
    {
        $resolver = $this->getOptionsResolver();
        $this->formType->configureOptions($resolver);

        $options = [
            'color_schema'  => 'short',
            'colors'        => [
                '#5484ED' => 'oro.form.color.bold_blue',
                '#A4BDFC' => 'oro.form.color.blue',
                '#46D6DB' => 'oro.form.color.turquoise',
                '#7AE7BF' => 'oro.form.color.green',
                '#51B749' => 'oro.form.color.bold_green',
                '#FBD75B' => 'oro.form.color.yellow',
                '#FFB878' => 'oro.form.color.orange',
                '#FF887C' => 'oro.form.color.red',
                '#DC2127' => 'oro.form.color.bold_red',
                '#DBADFF' => 'oro.form.color.purple',
                '#E1E1E1' => 'oro.form.color.gray'
            ],
        ];

        $resolvedOptions = $resolver->resolve($options);

        $this->assertEquals(
            [
                'colors'                => [
                    '#5484ED' => 'oro.form.color.bold_blue',
                    '#A4BDFC' => 'oro.form.color.blue',
                    '#46D6DB' => 'oro.form.color.turquoise',
                    '#7AE7BF' => 'oro.form.color.green',
                    '#51B749' => 'oro.form.color.bold_green',
                    '#FBD75B' => 'oro.form.color.yellow',
                    '#FFB878' => 'oro.form.color.orange',
                    '#FF887C' => 'oro.form.color.red',
                    '#DC2127' => 'oro.form.color.bold_red',
                    '#DBADFF' => 'oro.form.color.purple',
                    '#E1E1E1' => 'oro.form.color.gray'
                ],
                'translatable'          => true,
                'allow_empty_color'     => false,
                'empty_color'           => null,
                'picker'                => false,
                'picker_delay'          => 0,
                'color_schema'          => 'short',
                'empty_value'           => null,
                'allow_custom_color'    => false,
                'custom_color_control'  => null,

            ],
            $resolvedOptions
        );
    }

    public function testConfigureOptionsForUnknownColorSchema()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown color schema: "unknown".');

        $resolver = $this->getOptionsResolver();
        $this->formType->configureOptions($resolver);

        $options = [
            'color_schema'  => 'unknown',
        ];

        $resolver->resolve($options);
    }

    /**
     * @dataProvider buildViewDataProvider
     */
    public function testBuildView(array $options, array $expectedVars)
    {
        $form = $this->createMock(FormInterface::class);
        $view = new FormView();

        $this->formType->buildView($view, $form, $options);
        $this->assertEquals($expectedVars, $view->vars);
    }

    public function testGetParent()
    {
        $this->assertEquals(HiddenType::class, $this->formType->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_simple_color_picker', $this->formType->getName());
    }

    public function buildViewDataProvider(): array
    {
        return [
            [
                'options'       => [
                    'colors'                => [
                        '#FFFFFF' => '#FFFFFF',
                    ],
                    'translatable'          => false,
                    'allow_empty_color'     => true,
                    'empty_color'           => null,
                    'empty_value'           => null,
                    'allow_custom_color'    => true,
                    'custom_color_control'  => true,
                    'picker'                => true,
                    'picker_delay'          => 0,
                ],
                'expectedVars'  => [
                    'value'                 => null,
                    'attr'                  => [],
                    'allow_custom_color'    => true,
                    'configs'               => [
                        'picker'        => true,
                        'pickerDelay'   => 0,
                        'theme'         => 'fontawesome with-empty-color with-custom-color',
                        'custom_color'  => ['control' => true],
                        'data'          => [
                            [
                                'id'    => null,
                                'text'  => null,
                                'class' => 'empty-color',
                            ],
                            [],
                            [
                                'id'    => '#FFFFFF',
                                'text'  => '#FFFFFF',
                            ],
                            [],
                            [
                                'id'    => null,
                                'text'  => null,
                                'class' => 'custom-color',
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([]);

        return $resolver;
    }
}
