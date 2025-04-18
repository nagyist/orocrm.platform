<?php

namespace Oro\Bundle\ApiBundle\Processor\Subresource\Shared\Rest;

use Oro\Bundle\ApiBundle\Processor\Shared\Rest\AbstractAddPaginationLinks;
use Oro\Bundle\ApiBundle\Processor\Subresource\SubresourceContext;
use Oro\Component\ChainProcessor\ContextInterface;

/**
 * Adds "first", "prev" and "next" pagination links to a whole document of success response for a relationship.
 * @link https://jsonapi.org/format/#fetching-pagination
 */
class AddPaginationLinksForRelationship extends AbstractAddPaginationLinks
{
    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var SubresourceContext $context */

        $documentBuilder = $context->getResponseDocumentBuilder();
        if (null === $documentBuilder || !$context->isSuccessResponse()) {
            return;
        }

        $parentMetadata = $context->getParentMetadata();
        if (null === $parentMetadata) {
            return;
        }

        $requestType = $context->getRequestType();
        $parentEntityAlias = $documentBuilder->getEntityAlias($context->getParentClassName(), $requestType);
        $parentEntityId = $documentBuilder->getEntityId($context->getParentId(), $requestType, $parentMetadata);
        $baseLink = $this->getRouteLinkMetadata(
            $this->getRoutes($requestType)->getRelationshipRouteName(),
            [
                'entity'      => $parentEntityAlias,
                'id'          => $parentEntityId,
                'association' => $context->getAssociationName()
            ]
        );
        $this->addLinks(
            $documentBuilder,
            $baseLink,
            $this->getFilterNames($requestType)->getPageNumberFilterName(),
            $context->getFilterValues()
        );
    }
}
