<?php

namespace Oro\Bundle\ApiBundle\Processor\Subresource\UpdateRelationship;

use Oro\Bundle\ApiBundle\Form\Handler\UnidirectionalAssociationHandler;
use Oro\Bundle\ApiBundle\Processor\GetConfig\CompleteDefinition\UnidirectionalAssociationCompleter;
use Oro\Bundle\ApiBundle\Processor\Subresource\ChangeRelationshipContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Handles unidirectional associations for "update_relationship" action.
 */
class HandleUnidirectionalAssociations implements ProcessorInterface
{
    private UnidirectionalAssociationHandler $handler;

    public function __construct(UnidirectionalAssociationHandler $handler)
    {
        $this->handler = $handler;
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var ChangeRelationshipContext $context */

        $parentConfig = $context->getParentConfig();
        if (null === $parentConfig) {
            // not supported API resource
            return;
        }

        $associationName = $context->getAssociationName();
        $unidirectionalAssociations = $parentConfig->get(
            UnidirectionalAssociationCompleter::UNIDIRECTIONAL_ASSOCIATIONS
        );
        if (!$unidirectionalAssociations || !isset($unidirectionalAssociations[$associationName])) {
            // not unidirectional association
            return;
        }

        $this->handler->handleUpdate(
            $context->getForm(),
            $parentConfig,
            [$associationName => $unidirectionalAssociations[$associationName]],
            $context->getRequestType()
        );
    }
}
