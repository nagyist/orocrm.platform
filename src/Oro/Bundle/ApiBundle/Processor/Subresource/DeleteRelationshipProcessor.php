<?php

namespace Oro\Bundle\ApiBundle\Processor\Subresource;

use Oro\Bundle\ApiBundle\Processor\Subresource\DeleteRelationship\DeleteRelationshipContext;

/**
 * The main processor for "delete_relationship" action.
 */
class DeleteRelationshipProcessor extends SubresourceProcessor
{
    #[\Override]
    protected function createContextObject(): DeleteRelationshipContext
    {
        return new DeleteRelationshipContext($this->configProvider, $this->metadataProvider);
    }
}
