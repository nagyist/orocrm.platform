<?php

namespace Oro\Bundle\ApiBundle\Processor\GetList;

use Oro\Bundle\ApiBundle\Config\Extra\CustomizeLoadedDataConfigExtra;
use Oro\Bundle\ApiBundle\Config\Extra\DataTransformersConfigExtra;
use Oro\Bundle\ApiBundle\Config\Extra\EntityDefinitionConfigExtra;
use Oro\Bundle\ApiBundle\Config\Extra\FiltersConfigExtra;
use Oro\Bundle\ApiBundle\Config\Extra\SortersConfigExtra;
use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Sets an initial list of requests for configuration data.
 * It is supposed that the list was initialized if
 * the EntityDefinitionConfigExtra is already exist in the context.
 */
class InitializeConfigExtras implements ProcessorInterface
{
    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var Context $context */

        if ($context->hasConfigExtra(EntityDefinitionConfigExtra::NAME)) {
            // config extras are already initialized
            return;
        }

        $context->addConfigExtra(new EntityDefinitionConfigExtra($context->getAction(), true));
        $context->addConfigExtra(new CustomizeLoadedDataConfigExtra());
        $context->addConfigExtra(new DataTransformersConfigExtra());
        $context->addConfigExtra(new FiltersConfigExtra());
        $context->addConfigExtra(new SortersConfigExtra());
    }
}
