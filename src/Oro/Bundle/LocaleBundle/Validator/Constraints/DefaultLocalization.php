<?php

namespace Oro\Bundle\LocaleBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DefaultLocalization extends Constraint
{
    /**
     * @var string
     */
    public $service = 'oro_locale.default_localization_validator';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
