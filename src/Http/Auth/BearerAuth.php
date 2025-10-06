<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Auth;

use dsolodev\Cloudflare\Http\Contracts\AuthInterface;
use dsolodev\Cloudflare\Http\Exceptions\AuthException;
use Psr\Http\Message\RequestInterface;

final readonly class BearerAuth implements AuthInterface
{
    /**
     * @throws AuthException
     */
    public function __construct(
        private string $token,
        private string $email
    ) {
        if ($this->token === '' || $this->token === '0' || ($this->email === '' || $this->email === '0')) {
            throw new AuthException("Bearer authentication requires both 'token' and 'email'");
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array{RequestInterface, array<string, mixed>}
     */
    public function prepareRequest(RequestInterface $request, array $options = []): array {
        $request = $request
            ->withAddedHeader("Authorization", "Bearer {$this->token}")
            ->withAddedHeader("X-Auth-Email", $this->email);

        return [$request, $options];
    }
}
