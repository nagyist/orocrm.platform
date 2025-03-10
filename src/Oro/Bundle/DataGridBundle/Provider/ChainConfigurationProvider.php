<?php

namespace Oro\Bundle\DataGridBundle\Provider;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Exception\RuntimeException;

/**
 * Delegates the loading of datagrid configuration to child providers.
 */
class ChainConfigurationProvider implements ConfigurationProviderInterface
{
    /** @var iterable|ConfigurationProviderInterface[] */
    private $providers;

    /**
     * @param iterable|ConfigurationProviderInterface[] $providers
     */
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    #[\Override]
    public function isApplicable(string $gridName): bool
    {
        return true;
    }

    #[\Override]
    public function getConfiguration(string $gridName): DatagridConfiguration
    {
        foreach ($this->providers as $provider) {
            if ($provider->isApplicable($gridName)) {
                return $provider->getConfiguration($gridName);
            }
        }

        throw new RuntimeException(sprintf('A configuration for "%s" datagrid was not found.', $gridName));
    }

    /**
     * @return iterable|ConfigurationProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    #[\Override]
    public function isValidConfiguration(string $gridName): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->isApplicable($gridName) && $provider->isValidConfiguration($gridName)) {
                return true;
            }
        }

        return false;
    }
}
