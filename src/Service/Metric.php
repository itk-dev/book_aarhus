<?php

namespace App\Service;

use ItkDev\MetricsBundle\Service\MetricsService;

class Metric
{
    public function __construct(
        private readonly MetricsService $metricsService,
    ) {
    }

    public function counter($name, $description = null, $invokingInstance = null): void
    {
        $prefix = null;

        if (null !== $invokingInstance) {
            try {
                $prefix = (new \ReflectionClass($invokingInstance))->getShortName().'_';
            } catch (\ReflectionException) {
            }
        }

        $this->metricsService->counter($prefix.$name, $description ?? $name);
    }
}
