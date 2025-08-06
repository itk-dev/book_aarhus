<?php

namespace App\Service;

use ItkDev\MetricsBundle\Service\MetricsService;

class MetricsHelper
{
    public const INVOKE = 'invoke';
    public const COMPLETE = 'complete';
    public const FAILURE = 'failure';
    public const EXCEPTION = 'exception';

    public function __construct(
        private readonly MetricsService $metricsService,
    ) {
    }

    public function incMethodTotal(string $method, string $action): void
    {
        $metricMethodName = preg_replace('/^.*\\\s*/', '', $method);
        $metricMethodName = str_replace('::', '__', (string) $metricMethodName);
        $metricMethodName = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $metricMethodName));

        $this->metricsService->counter('method_'.$metricMethodName.'_total', 'Method '.$method.' totals.', 1, ['action' => $action]);
    }

    public function incExceptionTotal(string $exceptionClassName): void
    {
        $exceptionClassName = preg_replace('/^.*\\\s*/', '', $exceptionClassName);
        $exceptionClassName = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', (string) $exceptionClassName));
        $this->metricsService->counter('exception_total', 'Exception totals.', 1, [self::EXCEPTION => $exceptionClassName]);
    }
}
