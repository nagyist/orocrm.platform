<?php

namespace Oro\Bundle\TestFrameworkBundle\BehatStatisticExtension\AvgTimeProvider;

final class FeatureAvgTimeRegistry implements AvgTimeProviderInterface
{
    /**
     * @var AvgTimeProviderInterface[]
     */
    private $providers = [];

    /**
     * @var int|null
     */
    private $averageTime = null;

    #[\Override]
    public function getAverageTimeById($id)
    {
        foreach ($this->providers as $provider) {
            if ($time = $provider->getAverageTimeById($id)) {
                return $time;
            }
        }

        return $this->getAverageTime();
    }

    #[\Override]
    public function getAverageTime()
    {
        if (!is_null($this->averageTime)) {
            return $this->averageTime;
        }

        if (empty($this->providers)) {
            return 0;
        }

        $averages = array_map(function (AvgTimeProviderInterface $provider) {
            return $provider->getAverageTime();
        }, $this->providers);

        $averages = array_filter($averages);

        if (empty($averages)) {
            return 0;
        }

        $this->averageTime = round(array_sum($averages) / count($averages));

        return $this->averageTime;
    }

    public function addProvider(AvgTimeProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
