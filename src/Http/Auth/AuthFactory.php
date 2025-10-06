<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Auth;

use dsolodev\Cloudflare\Http\Contracts\AuthInterface;
use dsolodev\Cloudflare\Http\Exceptions\AuthException;

final class AuthFactory
{
    /**
     * @param array<string, string> $options
     *
     * @throws AuthException
     */
    public static function create(string $strategy, array $options): AuthInterface {
        return match ($strategy) {
            'Bearer' => new BearerAuth(
                $options['token'] ?? '',
                $options['email'] ?? ''
            ),
            'ApiKey' => new ApiKeyAuth(
                $options['email'] ?? '',
                $options['apiKey'] ?? ''
            ),
            default  => throw new AuthException("Invalid authentication strategy: $strategy")
        };
    }
}
