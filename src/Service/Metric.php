<?php

namespace App\Service;

use ItkDev\MetricsBundle\Service\MetricsService;

class Metric
{
    public const INVOKE = 'invoke';
    public const COMPLETE = 'complete';
    public const FAILURE = 'failure';
    public const EXCEPTION = 'exception';

    public function __construct(
        private readonly MetricsService $metricsService,
    ) {
    }

    public function totalIncByOne(string $name, ?string $description = null, $invokingInstance = null, array $labels = []): void
    {
        $prefix = null;

        if (null !== $invokingInstance) {
            try {
                $className = (new \ReflectionClass($invokingInstance))->getShortName();
                $output = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
                $prefix = $output.'_';
            } catch (\ReflectionException) {
            }
        }

        $this->metricsService->counter($prefix.$name.'_total', $description ?? '', 1, $labels);
    }

    public function incMethodTotal(string $method, string $action): void
    {
        $metricMethodName = preg_replace('/^.*\\\s*/', '', $method);
        $metricMethodName = str_replace('::', '__', $metricMethodName);
        $metricMethodName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $metricMethodName));

        $this->metricsService->counter('method_'.$metricMethodName.'_total', 'Method '.$method.' totals.', 1, ['action' => $action]);
    }

    public function incExceptionTotal($exceptionClassName): void
    {
        $exceptionClassName = preg_replace('/^.*\\\s*/', '', $exceptionClassName);
        $exceptionClassName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $exceptionClassName));
        $this->metricsService->counter('exception_total', 'Exception totals.', 1, [self::EXCEPTION => $exceptionClassName]);
    }
}
