<?php

namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Entity repository for Role entity.
 */
class RoleRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get a list of users assigned to the given role
     *
     * @param  Role $role
     * @return QueryBuilder
     */
    public function getUserQueryBuilder(Role $role)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->join('u.userRoles', 'role')
            ->where('role = :role')
            ->setParameter('role', $role);
    }

    /**
     * Checks if there are at least one user assigned to the given role
     *
     * @param Role $role
     * @return bool
     */
    public function hasAssignedUsers(Role $role)
    {
        $findResult = $this->getUserQueryBuilder($role)
            ->select('role.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return !empty($findResult);
    }

    /**
     * @param Role $role
     * @return User|null
     */
    public function getFirstMatchedUser(Role $role)
    {
        return $this
            ->getUserQueryBuilder($role)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
