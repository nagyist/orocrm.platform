<?php

namespace Oro\Bundle\LayoutBundle\Tests\Unit\Form\RendererEngine;

use Oro\Bundle\LayoutBundle\Form\RendererEngine\TwigRendererEngine;
use Oro\Bundle\LayoutBundle\Tests\Unit\Form\RendererEngineTest;
use Twig\Environment;

class TwigRendererEngineTest extends RendererEngineTest
{
    #[\Override]
    public function createRendererEngine()
    {
        return new TwigRendererEngine([], $this->createMock(Environment::class));
    }
}
