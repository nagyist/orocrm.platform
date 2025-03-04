<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The form type for filter by an enum entity.
 */
class EnumFilterType extends AbstractMultiChoiceType
{
    public const TYPE_IN = '1';
    public const TYPE_NOT_IN = '2';

    private EnumOptionsProvider $valueProvider;

    public function __construct(TranslatorInterface $translator, EnumOptionsProvider $valueProvider)
    {
        parent::__construct($translator);
        $this->valueProvider = $valueProvider;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                // either enum_code or class must be specified
                'enum_code'     => null,
                'class'         => null,
                'field_options' => [
                    'multiple' => true
                ],
                'operator_choices' => [
                    $this->translator->trans('oro.filter.form.label_type_in') => self::TYPE_IN,
                    $this->translator->trans('oro.filter.form.label_type_not_in') => self::TYPE_NOT_IN,
                ],
            ]
        );

        $resolver->setNormalizer(
            'class',
            function (Options $options, $value) {
                if ($value !== null) {
                    return $value;
                }

                if (empty($options['enum_code'])) {
                    throw new InvalidOptionsException('Either "class" or "enum_code" must option must be set.');
                }

                return EnumOption::class;
            }
        );

        // this normalizer allows to add/override field_options options outside
        $resolver->setNormalizer(
            'field_options',
            function (Options $options, $value) {
                if (isset($options['enum_code'])) {
                    $nullValue = null;
                    if ($options->offsetExists('null_value')) {
                        $nullValue = $options->offsetGet('null_value');
                    }
                    $value['choices'] = $this->getChoices($options['enum_code'], $nullValue);
                } else {
                    $value['choices'] = [];
                }
                if (!isset($value['translatable_options'])) {
                    $value['translatable_options'] = false;
                }
                if (!isset($value['multiple'])) {
                    $value['multiple'] = true;
                }

                return $value;
            }
        );
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * Convert value to string.
         * EnumOptionInterface declare primary key as string.
         * For enums with numerical PK value should be converted to string for correct types in DB query.
         */
        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($value) {
                    return $value;
                },
                function ($value) {
                    if (\is_array($value) && \array_key_exists('value', $value)) {
                        foreach ($value['value'] as &$data) {
                            $data = (string)$data;
                        }
                    }

                    return $value;
                }
            )
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceFilterType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_enum_filter';
    }

    private function getChoices(?string $enumCode, ?string $nullValue): array
    {
        $choices = [];
        if (!empty($nullValue)) {
            $choices[$this->translator->trans('oro.entity_extend.datagrid.enum.filter.empty')] = $nullValue;
        }

        if (!empty($enumCode)) {
            $choices = $this->valueProvider->getEnumChoicesByCode($enumCode) + $choices;
        }

        return $choices;
    }
}
