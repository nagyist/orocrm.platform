<?php

namespace Oro\Component\DoctrineUtils\ORM\Walker;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\AST;
use Doctrine\ORM\Query\ParserResult;

/**
 * Output SqlWalker decorator.
 * Calls all registered AST walkers before primary walker call, then calls all Result Modifier with passed SQL.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
trait DecoratedSqlWalkerTrait
{
    /**
     * @var OutputAstWalkerInterface[]
     */
    private $outputASTWalkers = [];

    /**
     * @var OutputResultModifierInterface[]
     */
    private $outputResultModifiers = [];

    public function __construct($query, $parserResult, array $queryComponents)
    {
        parent::__construct($query, $parserResult, $queryComponents);
        $this->initializeWalkers($query, $parserResult, $queryComponents);
    }

    private function initializeWalkers(AbstractQuery $query, ParserResult $parserResult, array $queryComponents)
    {
        if ($query->hasHint(OutputAstWalkerInterface::HINT_AST_WALKERS)) {
            foreach ($query->getHint(OutputAstWalkerInterface::HINT_AST_WALKERS) as $className) {
                $this->outputASTWalkers[] = new $className($query, $parserResult, $queryComponents);
            }
        }

        if ($query->hasHint(OutputResultModifierInterface::HINT_RESULT_MODIFIERS)) {
            foreach ($query->getHint(OutputResultModifierInterface::HINT_RESULT_MODIFIERS) as $className) {
                $this->outputResultModifiers[] = new $className($query, $parserResult, $queryComponents);
            }
        }
    }

    /**
     * @param string $functionName
     * @param array $arguments
     * @return string The SQL
     */
    private function makeDecoratedWalkCall($functionName, array $arguments)
    {
        foreach ($this->outputASTWalkers as $outputASTWalker) {
            $outputASTWalker->$functionName(...$arguments);
        }

        $result = parent::$functionName(...$arguments);

        foreach ($this->outputResultModifiers as $outputResultModifier) {
            $argumentsWithResult = $arguments;
            $argumentsWithResult[] = $result;
            $result = $outputResultModifier->$functionName(...$argumentsWithResult);
        }

        return $result;
    }

    public function walkSelectStatement(AST\SelectStatement $AST)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSelectClause($selectClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkFromClause($fromClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkFunction($function)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkOrderByClause($orderByClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkOrderByItem($orderByItem)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkHavingClause($havingClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkJoin($join)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSelectExpression($selectExpression)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkQuantifiedExpression($qExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSubselect($subselect)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSubselectFromClause($subselectFromClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSimpleSelectClause($simpleSelectClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSimpleSelectExpression($simpleSelectExpression)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkAggregateExpression($aggExpression)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkGroupByClause($groupByClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkGroupByItem($groupByItem)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkUpdateStatement(AST\UpdateStatement $AST)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkDeleteStatement(AST\DeleteStatement $AST)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkDeleteClause(AST\DeleteClause $deleteClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkUpdateClause($updateClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkUpdateItem($updateItem)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkWhereClause($whereClause)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkConditionalExpression($condExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkConditionalTerm($condTerm)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkConditionalFactor($factor)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkConditionalPrimary($primary)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkExistsExpression($existsExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkCollectionMemberExpression($collMemberExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkEmptyCollectionComparisonExpression($emptyCollCompExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkNullComparisonExpression($nullCompExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkInExpression($inExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkInstanceOfExpression($instanceOfExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkLiteral($literal)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkBetweenExpression($betweenExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkLikeExpression($likeExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkStateFieldPathExpression($stateFieldPathExpression)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkComparisonExpression($compExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkInputParameter($inputParam)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkArithmeticExpression($arithmeticExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkArithmeticTerm($term)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkStringPrimary($stringPrimary)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkArithmeticFactor($factor)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkSimpleArithmeticExpression($simpleArithmeticExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkPathExpression($pathExpr)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }

    public function walkResultVariable($resultVariable)
    {
        return $this->makeDecoratedWalkCall(__FUNCTION__, func_get_args());
    }
}
