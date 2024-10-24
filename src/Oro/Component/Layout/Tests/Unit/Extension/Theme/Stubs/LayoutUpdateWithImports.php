<?php

namespace Oro\Component\Layout\Tests\Unit\Extension\Theme\Stubs;

use Oro\Component\Layout\ImportsAwareLayoutUpdateInterface;
use Oro\Component\Layout\LayoutItemInterface;
use Oro\Component\Layout\LayoutManipulatorInterface;
use Oro\Component\Layout\LayoutUpdateInterface;

class LayoutUpdateWithImports implements LayoutUpdateInterface, ImportsAwareLayoutUpdateInterface
{
    /**
     * @return array
     */
    #[\Override]
    public function getImports()
    {
    }

    #[\Override]
    public function updateLayout(LayoutManipulatorInterface $layoutManipulator, LayoutItemInterface $item)
    {
    }
}
