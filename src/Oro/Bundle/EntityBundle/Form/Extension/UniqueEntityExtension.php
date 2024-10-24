<?php

namespace Oro\Bundle\EntityBundle\Form\Extension;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Extension\Traits\FormExtendedTypeTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds UniqueEntity constraint to the class metadata.
 */
class UniqueEntityExtension extends AbstractTypeExtension
{
    use FormExtendedTypeTrait;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ConfigProvider */
    protected $entityConfigProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ConfigProvider $entityConfigProvider,
        DoctrineHelper $doctrineHelper
    ) {
        $this->validator            = $validator;
        $this->translator           = $translator;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->doctrineHelper       = $doctrineHelper;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['data_class'])) {
            return;
        }

        $className = $options['data_class'];
        if (!$this->doctrineHelper->isManageableEntity($className)) {
            return;
        }
        if (!$this->entityConfigProvider->hasConfig($className)) {
            return;
        }

        $uniqueKeys = $this->entityConfigProvider->getConfig($className)->get('unique_key');
        if (empty($uniqueKeys)) {
            return;
        }

        /* @var \Symfony\Component\Validator\Mapping\ClassMetadata $validatorMetadata */
        $validatorMetadata = $this->validator->getMetadataFor($className);

        foreach ($uniqueKeys['keys'] as $uniqueKey) {
            $fields = $uniqueKey['key'];

            $labels = array_map(
                function ($fieldName) use ($className) {
                    $label = (string) $this
                        ->entityConfigProvider
                        ->getConfig($className, $fieldName)
                        ->get('label');

                    return $this->translator->trans($label);
                },
                $fields
            );

            $constraint = new UniqueEntity(
                [
                    'fields'    => $fields,
                    'errorPath' => '',
                    'message'   => $this
                        ->translator
                        ->trans(
                            'oro.entity.validation.unique_field',
                            ['%count%' => sizeof($fields), '%field%' => implode(', ', $labels)]
                        ),
                ]
            );

            $validatorMetadata->addConstraint($constraint);
        }
    }
}
