<?php

namespace Oro\Bundle\SecurityBundle\ConfigExpression;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Authorization\AuthorizationCheckerTrait;
use Oro\Component\Action\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Checks whether an access to a resource is granted.
 */
class AclGranted extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;
    use AuthorizationCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var mixed */
    protected $attributes;

    /** @var mixed */
    protected $object;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor,
        ManagerRegistry $doctrine
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
        $this->doctrine = $doctrine;
    }

    #[\Override]
    public function getName()
    {
        return 'acl';
    }

    #[\Override]
    public function toArray()
    {
        $params = [$this->attributes];
        if ($this->object !== null) {
            $params[] = $this->object;
        }

        return $this->convertToArray($params);
    }

    #[\Override]
    public function compile($factoryAccessor)
    {
        $params = [$this->attributes];
        if ($this->object !== null) {
            $params[] = $this->object;
        }

        return $this->convertToPhpCode($params, $factoryAccessor);
    }

    /**
     *
     * @acl: ['contact_view']
     * @acl: ['EDIT', 'Acme\DemoBundle\Entity\Contact']
     *
     * {@see \Oro\Bundle\SecurityBundle\Authorization\AuthorizationChecker::isGranted} for details.
     */
    #[\Override]
    public function initialize(array $options)
    {
        $count = count($options);
        if ($count >= 1 && $count <= 2) {
            $this->attributes = reset($options);
            if (!$this->attributes) {
                throw new InvalidArgumentException('ACL attributes must not be empty.');
            }
            if ($count > 1) {
                $this->object = next($options);
                if (!$this->object) {
                    throw new InvalidArgumentException('ACL object must not be empty.');
                }
            }
        } else {
            throw new InvalidArgumentException(
                sprintf('Options must have 1 or 2 elements, but %d given.', count($options))
            );
        }

        return $this;
    }

    #[\Override]
    protected function isConditionAllowed($context)
    {
        if (!$this->tokenAccessor->getToken()) {
            return false;
        }
        $attributes = $this->resolveValue($context, $this->attributes);
        $object     = $this->resolveValue($context, $this->object);

        if (\is_object($object)) {
            $class = ClassUtils::getRealClass($object);
            $objectManager = $this->doctrine->getManagerForClass($class);
            if ($objectManager instanceof EntityManagerInterface) {
                $unitOfWork = $objectManager->getUnitOfWork();
                if ($unitOfWork->isScheduledForInsert($object) || !$unitOfWork->isInIdentityMap($object)) {
                    $object = 'entity:' . $class;
                }
            }
        }

        return $this->isAttributesGranted(
            $this->authorizationChecker,
            $attributes,
            $object
        );
    }
}
