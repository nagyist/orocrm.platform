<?php

namespace Oro\Bundle\SecurityBundle\AccessRule\Expr;

use Oro\Bundle\SecurityBundle\AccessRule\Visitor;

/**
 * Access rule expression that allows to build Exists subqueries.
 */
class Exists implements ExpressionInterface
{
    /** @var bool */
    private $not;

    /** @var Subquery */
    private $expression;

    public function __construct(Subquery $expression, bool $not = false)
    {
        $this->expression = $expression;
        $this->not = $not;
    }

    public function isNot(): bool
    {
        return $this->not;
    }

    public function getExpression(): Subquery
    {
        return $this->expression;
    }

    #[\Override]
    public function visit(Visitor $visitor)
    {
        return $visitor->walkExists($this);
    }
}
