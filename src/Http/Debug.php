<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http;

use Stringable;
use Throwable;

final class Debug implements Stringable
{
    public string $lastRequestBody = '';

    /** @var array<string> */
    public array $lastRequestHeaders = [];

    public ?int $lastResponseCode = null;

    /** @var array<string> */
    public array $lastResponseHeaders = [];

    public ?Throwable $lastResponseError = null;

    public function __toString(): string {
        $lastError = $this->lastResponseError?->getMessage() ?? 'No error';

        $output = "Last Response Code: " . $this->lastResponseCode . "\n";
        $output .= "Last Response Error: " . $lastError . "\n";
        $output .= "Last Response Headers: " . json_encode($this->filterSensitiveData($this->lastResponseHeaders)) . "\n";
        $output .= "Last Request Headers: " . json_encode($this->filterSensitiveData($this->lastRequestHeaders)) . "\n";

        return $output . ("Last Request Body: [FILTERED]\n");
    }

    /**
     * Filter sensitive data from debug output
     *
     * @param array<string> $data
     *
     * @return array<string>
     */
    private function filterSensitiveData(array $data): array {
        $sensitiveHeaders = [
            'authorization',
            'x-auth-key',
            'x-auth-email',
            'cookie',
            'set-cookie',
        ];

        $filtered = [];
        foreach ($data as $name => $value) {
            $filtered[$name] = in_array(mb_strtolower($name), $sensitiveHeaders, true) ? '[FILTERED]' : $value;
        }

        return $filtered;
    }
}
