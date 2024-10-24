<?php

namespace Oro\Bundle\EntityExtendBundle\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * Constraint to check is given value correct decimal number with expected precision and scale
 */
#[Attribute]
class Decimal extends Constraint
{
    /**
     * @var string
     */
    public $message = 'This value should be decimal with valid precision ({{ precision }}) and scale ({{ scale }}).';

    /**
     * @var string
     */
    public $messageNotNumeric = 'This value should be of type numeric.';

    /**
     * For more info look here: \Doctrine\DBAL\Platforms\AbstractPlatform::getDecimalTypeDeclarationSQL
     *
     * @var string
     */
    public $precision = 10;

    /**
     * For more info look here: \Doctrine\DBAL\Platforms\AbstractPlatform::getDecimalTypeDeclarationSQL
     *
     * @var string
     */
    public $scale = 0;

    public function __construct($options = null)
    {
        if (is_array($options)) {
            foreach (['precision', 'scale'] as $optionName) {
                if (array_key_exists($optionName, $options)) {
                    if (filter_var($options[$optionName], FILTER_VALIDATE_INT)) {
                        $this->$optionName = $options[$optionName];
                    }
                    unset($options[$optionName]);
                }
            }
        }

        parent::__construct($options);
    }
}
