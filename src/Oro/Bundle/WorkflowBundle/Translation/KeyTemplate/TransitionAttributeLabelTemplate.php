<?php

namespace Oro\Bundle\WorkflowBundle\Translation\KeyTemplate;

/**
 * Transition attribute label key template.
 */
class TransitionAttributeLabelTemplate extends TransitionAttributeTemplate
{
    const NAME = 'transition_attribute_label';

    #[\Override]
    public function getTemplate(): string
    {
        return parent::getTemplate() . '.label';
    }
}
