<?php

namespace Oro\Bundle\ActionBundle\Helper;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Contains methods for action Context/parameters
 */
class ContextHelper
{
    const ROUTE_PARAM = 'route';
    const ROUTE_PARAMETERS = 'route_params';
    const ENTITY_ID_PARAM = 'entityId';
    const ENTITY_CLASS_PARAM = 'entityClass';
    const DATAGRID_PARAM = 'datagrid';
    const GROUP_PARAM = 'group';
    const FROM_URL_PARAM = 'fromUrl';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RequestStack */
    protected $requestStack;

    /** @var array */
    protected $actionDatas = [];

    /** @var  PropertyAccessorInterface */
    protected $propertyAccessor;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        PropertyAccessorInterface $propertyAccessor,
        ?RequestStack $requestStack = null
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->propertyAccessor = $propertyAccessor;
        $this->requestStack = $requestStack;
    }

    /**
     * @param array|null $context
     * @return array
     */
    public function getContext(?array $context = null)
    {
        if (null === $context) {
            $route = $this->getRequestParameter(self::ROUTE_PARAM) ?: $this->getRequestParameter('_route');
            $routeParameters = $this->getRequestParameter('_route_params', []);
            $context = [
                self::ROUTE_PARAM => $route,
                self::ROUTE_PARAMETERS => urlencode(json_encode($routeParameters)),
                self::ENTITY_ID_PARAM => $this->getRequestParameter(self::ENTITY_ID_PARAM),
                self::ENTITY_CLASS_PARAM => $this->getRequestParameter(self::ENTITY_CLASS_PARAM),
                self::DATAGRID_PARAM => $this->getRequestParameter(self::DATAGRID_PARAM),
                self::GROUP_PARAM => $this->getRequestParameter(self::GROUP_PARAM),
                self::FROM_URL_PARAM => $this->getRequestParameter(self::FROM_URL_PARAM),
            ];
        }

        return $this->normalizeContext($context);
    }

    /**
     * @param array $context
     * @return array
     * @throws \RuntimeException
     */
    public function getActionParameters(array $context)
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            throw new \RuntimeException('The main request is not defined.');
        }
        $params = [
            self::ROUTE_PARAM => $request->attributes->get('_route'),
            self::ROUTE_PARAMETERS => $request->attributes->get('_route_params', []),
            self::FROM_URL_PARAM => $request->getRequestUri(),
        ];

        $entity = array_key_exists('entity', $context) && is_object($context['entity']) ? $context['entity'] : null;

        if ($entity &&
            $this->doctrineHelper->isManageableEntity($entity) &&
            !$this->doctrineHelper->isNewEntity($context['entity'])
        ) {
            $params[self::ENTITY_ID_PARAM] = $this->doctrineHelper->getEntityIdentifier($context['entity']);
            $params[self::ENTITY_CLASS_PARAM] = ClassUtils::getClass($context['entity']);
        } elseif (isset($context['entity_class'])) {
            $params[self::ENTITY_CLASS_PARAM] = $context['entity_class'];
        }

        return $params;
    }

    /**
     * @param array|null $context
     * @return ActionData
     */
    public function getActionData(?array $context = null)
    {
        $context = $this->getContext($context);

        $hash = $this->generateHash($context, [self::ENTITY_CLASS_PARAM, self::ENTITY_ID_PARAM, self::DATAGRID_PARAM]);

        if (!array_key_exists($hash, $this->actionDatas)) {
            $entity = null;

            if ($context[self::ENTITY_CLASS_PARAM]) {
                $entity = $this->getEntityReference(
                    $context[self::ENTITY_CLASS_PARAM],
                    $context[self::ENTITY_ID_PARAM]
                );
            }

            $data = ['data' => $entity, ActionData::OPERATION_TOKEN => $hash];

            if ($context[self::DATAGRID_PARAM]) {
                $data['gridName'] = $context[self::DATAGRID_PARAM];
            }

            $this->actionDatas[$hash] = new ActionData($data);
        }

        return $this->actionDatas[$hash];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getRequestParameter(string $name, $default = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request ? $request->get($name, $default) : $default;
    }

    /**
     * @param array $context
     * @return array
     */
    protected function normalizeContext(array $context)
    {
        return array_merge(
            [
                self::ROUTE_PARAM => null,
                self::ENTITY_ID_PARAM => null,
                self::ENTITY_CLASS_PARAM => null,
                self::DATAGRID_PARAM => null,
                self::GROUP_PARAM => null,
                self::FROM_URL_PARAM => null,
            ],
            $context
        );
    }

    /**
     * @param string $entityClass
     * @param mixed $entityId
     * @return Object
     */
    protected function getEntityReference($entityClass, $entityId)
    {
        $entity = null;

        if ($this->isClassExists($entityClass) && $this->doctrineHelper->isManageableEntity($entityClass)) {
            if ($entityId) {
                $entity = $this->doctrineHelper->getEntityReference($entityClass, $entityId);
            } else {
                $entity = $this->doctrineHelper->createEntityInstance($entityClass);
            }
        }

        return $entity;
    }

    /**
     * @param array $context
     * @param array $properties
     * @return string
     */
    protected function generateHash(array $context, array $properties)
    {
        $array = [];
        foreach ($properties as $property) {
            $array[$property] = $this->propertyAccessor->getValue($context, sprintf('[%s]', $property));
            if (is_array($array[$property])) {
                ksort($array[$property]);
            }
        }
        ksort($array);

        return md5(json_encode($array, JSON_NUMERIC_CHECK));
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function isClassExists($className)
    {
        if (class_exists($className)) {
            $reflection = new \ReflectionClass($className);

            return !$reflection->isAbstract();
        }

        return false;
    }
}
