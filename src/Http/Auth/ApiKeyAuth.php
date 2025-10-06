<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Auth;

use dsolodev\Cloudflare\Http\Contracts\AuthInterface;
use dsolodev\Cloudflare\Http\Exceptions\AuthException;
use Psr\Http\Message\RequestInterface;

final readonly class ApiKeyAuth implements AuthInterface
{
    /**
     * @throws AuthException
     */
    public function __construct(
        private string $email,
        private string $apiKey
    ) {
        if ($this->email === '' || $this->email === '0' || ($this->apiKey === '' || $this->apiKey === '0')) {
            throw new AuthException("API key authentication requires both 'email' and 'apiKey'");
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array{RequestInterface, array<string, mixed>}
     */
    public function prepareRequest(RequestInterface $request, array $options = []): array {
        $request = $request
            ->withAddedHeader("X-Auth-Email", $this->email)
            ->withAddedHeader("X-Auth-Key", $this->apiKey);

        return [$request, $options];
    }
}
