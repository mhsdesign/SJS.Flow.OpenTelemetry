<?php

declare(strict_types=1);

namespace SJS\Flow\OpenTelemetry\Log;

use Neos\Flow\Log\ThrowableStorageInterface;
use OpenTelemetry\SDK\Trace\Span;

class OpenTelemetryThrowableStorage implements ThrowableStorageInterface
{
    public static function createWithOptions(array $options): ThrowableStorageInterface
    {
        return new self();
    }

    public function logThrowable(\Throwable $throwable, array $additionalData = []): string
    {
        $span = Span::getCurrent();

        $prefixedFlowData = [];
        foreach ($additionalData as $key => $value) {
            // todo deep arrays not supported
            $prefixedFlowData["flow.additionalData.$key"] = $value;
        }

        // todo evaluate to render stacktrace like neos with method arguments and previous exceptions traces
        $span->recordException(
            $throwable,
            $prefixedFlowData
        );

        // NOT ANY PRACTICE WHEN USING OT - adjust flow to not use return value
        return sprintf('%s - See %s', $throwable->getMessage(), $span->getContext()->getSpanId());
    }

    public function setRequestInformationRenderer(\Closure $requestInformationRenderer)
    {
        return $this;
    }

    public function setBacktraceRenderer(\Closure $backtraceRenderer)
    {
        return $this;
    }
}
