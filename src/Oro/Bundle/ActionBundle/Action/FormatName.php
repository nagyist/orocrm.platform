<?php

namespace Oro\Bundle\ActionBundle\Action;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;
use Oro\Component\ConfigExpression\ContextAccessor;

class FormatName extends AbstractAction
{
    /**
     * @var EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var array
     */
    protected $options;

    public function __construct(ContextAccessor $contextAccessor, EntityNameResolver $entityNameResolver)
    {
        parent::__construct($contextAccessor);

        $this->entityNameResolver = $entityNameResolver;
    }

    #[\Override]
    public function initialize(array $options)
    {
        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        }
        if (empty($options['object'])) {
            throw new InvalidParameterException('Object parameter is required');
        }

        $this->options = $options;

        return $this;
    }

    #[\Override]
    protected function executeAction($context)
    {
        $this->contextAccessor->setValue(
            $context,
            $this->options['attribute'],
            $this->entityNameResolver->getName(
                $this->contextAccessor->getValue($context, $this->options['object'])
            )
        );
    }
}
