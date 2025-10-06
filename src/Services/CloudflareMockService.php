<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Services;

use Illuminate\Support\Facades\Log;

final readonly class CloudflareMockService
{
    public function __construct(private bool $loggingEnabled = false) {
    }

    /**
     * @param array<string, mixed> $arguments
     *
     */
    public function __call(string $name, array $arguments): self {
        if ($this->loggingEnabled) {
            Log::debug("Mock Cloudflare API call: $name with arguments: " . json_encode($arguments));

            return new self($this->loggingEnabled);
        }

        return $this;
    }
}
