<?php

namespace Oro\Bundle\LayoutBundle\Twig\Node;

use Oro\Bundle\LayoutBundle\Twig\TwigRenderer;
use Twig\Compiler;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;

/**
 * Implementation of block_* TWIG functions
 */
class SearchAndRenderBlockNode extends FunctionExpression
{
    /**
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    #[\Override]
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        $compiler->raw(
            sprintf(
                '$this->env->getRuntime("%s")->searchAndRenderBlock(',
                TwigRenderer::class
            )
        );

        $name            = $this->getAttribute('name');
        $blockNameSuffix = substr($name, strrpos($name, '_') + 1);
        $label           = null;
        $arguments       = iterator_to_array($this->getNode('arguments'));

        if ($name == 'parent_block_widget') {
            $compiler->raw('$context[\'block\']');
            $compiler->raw(', \'' . $blockNameSuffix . '\'');
            $compiler->raw(', $context');
            $compiler->raw(', true');
        } elseif (isset($arguments[0])) {
            $compiler->subcompile($arguments[0]);
            $compiler->raw(', \'' . $blockNameSuffix . '\'');

            if (isset($arguments[1])) {
                if ('label' === $blockNameSuffix) {
                    // The "label" function expects the label in the second and
                    // the variables in the third argument
                    $label     = $arguments[1];
                    $variables = isset($arguments[2]) ? $arguments[2] : null;
                    $lineno    = $label->getTemplateLine();

                    if ($label instanceof ConstantExpression) {
                        // If the label argument is given as a constant, we can either
                        // strip it away if it is empty, or integrate it into the array
                        // of variables at compile time.
                        $labelIsExpression = false;

                        // Only insert the label into the array if it is not empty
                        if (!twig_test_empty($label->getAttribute('value'))) {
                            $originalVariables = $variables;
                            $variables         = new ArrayExpression(array(), $lineno);
                            $labelKey          = new ConstantExpression('label', $lineno);

                            if (null !== $originalVariables) {
                                foreach ($originalVariables->getKeyValuePairs() as $pair) {
                                    // Don't copy the original label attribute over if it exists
                                    if ((string)$labelKey !== (string)$pair['key']) {
                                        $variables->addElement($pair['value'], $pair['key']);
                                    }
                                }
                            }

                            // Insert the label argument into the array
                            $variables->addElement($label, $labelKey);
                        }
                    } else {
                        // The label argument is not a constant, but some kind of
                        // expression. This expression needs to be evaluated at runtime.
                        // Depending on the result (whether it is null or not), the
                        // label in the arguments should take precedence over the label
                        // in the attributes or not.
                        $labelIsExpression = true;
                    }
                } else {
                    // All other functions than "label" expect the variables
                    // in the second argument
                    $label             = null;
                    $variables         = $arguments[1];
                    $labelIsExpression = false;
                }

                if (null !== $variables || $labelIsExpression) {
                    $compiler->raw(', ');

                    if (null !== $variables) {
                        $compiler->subcompile($variables);
                    }

                    if ($labelIsExpression) {
                        if (null !== $variables) {
                            $compiler->raw(' + ');
                        }

                        // Check at runtime whether the label is empty.
                        // If not, add it to the array at runtime.
                        $compiler->raw('(twig_test_empty($_label_ = ');
                        $compiler->subcompile($label);
                        $compiler->raw(') ? array() : array("label" => $_label_))');
                    }
                }
            }
        }

        $compiler->raw(")");
    }
}
