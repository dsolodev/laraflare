<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Exceptions;

final class MissingParametersException extends CloudflareException
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(string $method, array $params, int $code = 0, ?CloudflareException $previous = null) {
        parent::__construct("Missing parameters for method $method: " . implode("', '", $params), $code, $previous);
    }
}
