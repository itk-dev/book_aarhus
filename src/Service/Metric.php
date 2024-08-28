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

    public function incFunctionTotal($invokingInstance, string $functionName, string $action): void
    {
        $classShortName = (new \ReflectionClass($invokingInstance))->getShortName();
        $className = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $classShortName));
        $functionName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $functionName));

        $this->metricsService->counter('function_total', 'Function totals.', 1, [$className.'__'.$functionName => $action]);
    }

    public function incExceptionTotal($exceptionClassName): void
    {
        $exceptionClassName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $exceptionClassName));
        $this->metricsService->counter('exception', 'Exception totals.', 1, [self::EXCEPTION => $exceptionClassName]);
    }
}
