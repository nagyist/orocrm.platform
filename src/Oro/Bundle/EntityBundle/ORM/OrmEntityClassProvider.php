<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Oro\Bundle\EntityBundle\Provider\EntityClassProviderInterface;

class OrmEntityClassProvider implements EntityClassProviderInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ManagerBagInterface */
    protected $managerBag;

    public function __construct(DoctrineHelper $doctrineHelper, ManagerBagInterface $managerBag)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->managerBag = $managerBag;
    }

    #[\Override]
    public function getClassNames()
    {
        $result = [];
        $managers = $this->managerBag->getManagers();
        if (!$managers) {
            return $result;
        }

        foreach ($managers as $om) {
            $allMetadata = $this->doctrineHelper->getAllShortMetadata($om);
            if (!$allMetadata) {
                continue;
            }

            foreach ($allMetadata as $metadata) {
                if (!$metadata->isMappedSuperclass) {
                    $result[] = $metadata->name;
                }
            }
        }

        return $result;
    }
}
