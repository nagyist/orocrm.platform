<?php

namespace Oro\Bundle\TagBundle;

use Oro\Bundle\TagBundle\DependencyInjection\Compiler\TagManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroTagBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new TagManagerPass());
    }
}
