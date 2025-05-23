<?php

namespace Oro\Bundle\WorkflowBundle\Translation\KeyTemplate;

use Oro\Bundle\TranslationBundle\Translation\TranslationKeyTemplateInterface;

/**
 * Workflow label key template.
 */
class WorkflowTemplate implements TranslationKeyTemplateInterface
{
    const NAME = 'workflow';
    const KEY_PREFIX = 'oro.workflow';

    /**
     * @return string
     */
    #[\Override]
    public function getName()
    {
        return static::NAME;
    }

    #[\Override]
    public function getTemplate(): string
    {
        return self::KEY_PREFIX . '.{{ workflow_name }}';
    }

    /**
     * @return array
     */
    #[\Override]
    public function getRequiredKeys()
    {
        return ['workflow_name'];
    }

    /**
     * @return array
     */
    #[\Override]
    public function getKeyTemplates()
    {
        $result = [];
        foreach ($this->getRequiredKeys() as $key) {
            $result[$key] = $this->getKeyTemplate($key);
        }

        return $result;
    }

    /**
     * @param string $attributeName
     * @return string
     */
    #[\Override]
    public function getKeyTemplate($attributeName)
    {
        return sprintf('{{ %s }}', $attributeName);
    }
}
