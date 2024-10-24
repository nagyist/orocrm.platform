<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Doctrine\Persistence\ManagerRegistry;

class ManagerBag implements ManagerBagInterface
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var string[] */
    protected $managerNames;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Registers a manager that may contain entities.
     *
     * @param string $managerName
     */
    public function addManager($managerName)
    {
        if (null === $this->managerNames) {
            $this->managerNames = [];
        }
        $this->managerNames[] = $managerName;
    }

    #[\Override]
    public function getManagers()
    {
        $result = [];
        // add default manager
        $result[] = $this->doctrine->getManager();
        // add other managers
        if (!empty($this->managerNames)) {
            foreach ($this->managerNames as $managerName) {
                $result[] = $this->doctrine->getManager($managerName);
            }
        }

        return $result;
    }
}
