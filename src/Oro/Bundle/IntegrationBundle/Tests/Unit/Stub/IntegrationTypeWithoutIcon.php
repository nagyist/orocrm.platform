<?php

namespace Oro\Bundle\IntegrationBundle\Tests\Unit\Stub;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface as IntegrationInterface;

class IntegrationTypeWithoutIcon implements IntegrationInterface
{
    #[\Override]
    public function getLabel()
    {
        return 'oro.type2.label';
    }
}
