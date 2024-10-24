<?php

namespace Oro\Bundle\LayoutBundle\Layout\Extension\Generator;

use Oro\Component\Layout\Exception\SyntaxException;
use Oro\Component\Layout\Loader\Generator\ConfigLayoutUpdateGeneratorExtensionInterface;
use Oro\Component\Layout\Loader\Generator\GeneratorData;
use Oro\Component\Layout\Loader\Visitor\VisitorCollection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionGeneratorExtension implements ConfigLayoutUpdateGeneratorExtensionInterface
{
    const NODE_CONDITIONS = 'conditions';

    /** @var ExpressionLanguage */
    protected $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    #[\Override]
    public function prepare(GeneratorData $data, VisitorCollection $visitorCollection)
    {
        $source = $data->getSource();
        if (is_array($source) && !empty($source[self::NODE_CONDITIONS])) {
            try {
                $expr = $this->expressionLanguage->parse($source[self::NODE_CONDITIONS], ['context']);
                if ($expr) {
                    $visitorCollection->append(new ExpressionConditionVisitor($expr, $this->expressionLanguage));
                }
            } catch (\Exception $e) {
                throw new SyntaxException(
                    'invalid conditions. ' . $e->getMessage(),
                    $source[self::NODE_CONDITIONS],
                    self::NODE_CONDITIONS
                );
            }
        }
    }
}
