<?php

namespace Oro\Bundle\DashboardBundle\Provider\Converters;

use Oro\Bundle\DashboardBundle\Provider\ConfigValueConverterAbstract;

class WidgetItemsChoiceTypeConverter extends ConfigValueConverterAbstract
{
    const ALL_ITEMS = 'all';

    #[\Override]
    public function getConvertedValue(array $widgetConfig, $value = null, array $config = [], array $options = [])
    {
        if ($value === null) {
            return $this->getDefaultChoices($config);
        }

        return parent::getConvertedValue($widgetConfig, $value, $config, $options);
    }

    #[\Override]
    public function getFormValue(array $config, $value)
    {
        if ($value === null) {
            return $this->getDefaultChoices($config);
        }

        return parent::getFormValue($config, $value);
    }

    #[\Override]
    public function getViewValue($value)
    {
        return implode(',', $value);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function getDefaultChoices(array $config)
    {
        if ($config['converter_attributes']['default_selected'] === self::ALL_ITEMS) {
            $values = [];
            foreach ($config['options']['choices'] as $option) {
                $values = array_merge($values, array_values($option));
            }
        } else {
            $values = $config['converter_attributes']['default_selected'];
        }

        return $values;
    }
}
