<?php

namespace Oro\Bundle\EntityConfigBundle\Attribute\Type;

use Laminas\Stdlib\Guard\ArrayOrTraversableGuardTrait;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;

/**
 * Provides metadata about multi-enum attribute type.
 */
class MultiEnumAttributeType extends EnumAttributeType
{
    use ArrayOrTraversableGuardTrait;

    #[\Override]
    public function isSortable(FieldConfigModel $attribute)
    {
        return false;
    }

    #[\Override]
    public function getSearchableValue(FieldConfigModel $attribute, $originalValue, ?Localization $localization = null)
    {
        $this->ensureTraversable($originalValue);

        $values = [];
        foreach ($originalValue as $enum) {
            $values[] = parent::getSearchableValue($attribute, $enum, $localization);
        }

        return implode(' ', $values);
    }

    #[\Override]
    public function getFilterableValue(FieldConfigModel $attribute, $originalValue, ?Localization $localization = null)
    {
        $this->ensureTraversable($originalValue);

        $value = [];

        /** @var EnumOptionInterface[] $originalValue */
        foreach ($originalValue as $enum) {
            $this->ensureSupportedType($enum);

            $key = sprintf('%s_enum.%s', $attribute->getFieldName(), $enum->getInternalId());

            $value[$key] = 1;
        }

        return $value;
    }

    #[\Override]
    public function getSortableValue(FieldConfigModel $attribute, $originalValue, ?Localization $localization = null)
    {
        throw new \RuntimeException('Not supported');
    }

    /**
     * @param string $originalValue
     * @throws \InvalidArgumentException
     */
    protected function ensureTraversable($originalValue): void
    {
        $this->guardForArrayOrTraversable($originalValue, 'Value', \InvalidArgumentException::class);
    }
}
