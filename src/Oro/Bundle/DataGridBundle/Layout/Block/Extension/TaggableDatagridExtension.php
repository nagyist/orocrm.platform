<?php

namespace Oro\Bundle\DataGridBundle\Layout\Block\Extension;

use Oro\Component\Layout\AbstractBlockTypeExtension;
use Oro\Component\Layout\Block\OptionsResolver\OptionsResolver;
use Oro\Component\Layout\Block\Type\Options;
use Oro\Component\Layout\BlockInterface;
use Oro\Component\Layout\BlockView;
use Oro\Component\Layout\Util\BlockUtils;

/**
 * This extension extends all links with "enable_tagging" option, that
 * can be used to enable watching of changes in the grid.
 */
class TaggableDatagridExtension extends AbstractBlockTypeExtension
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['enable_tagging' => false]);
    }

    #[\Override]
    public function buildView(BlockView $view, BlockInterface $block, Options $options)
    {
        BlockUtils::setViewVarsFromOptions($view, $options, ['enable_tagging']);
    }

    #[\Override]
    public function finishView(BlockView $view, BlockInterface $block)
    {
        BlockUtils::registerPlugin($view, 'taggable_datagrid');
    }

    #[\Override]
    public function getExtendedType()
    {
        return 'datagrid';
    }
}
