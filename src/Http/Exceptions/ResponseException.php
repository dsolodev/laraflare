<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Exceptions;

final class ResponseException extends CloudflareException
{
    public function __construct(string $method, string $detail = "", int $code = 0, ?CloudflareException $previous = null) {
        parent::__construct(
            "Response to $method is not valid. Check " . '$client->getDebug()' . " for more details. $detail",
            $code,
            $previous
        );
    }
}
