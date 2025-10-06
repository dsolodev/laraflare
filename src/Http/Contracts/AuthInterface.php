<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Contracts;

use Psr\Http\Message\RequestInterface;

interface AuthInterface
{
    /**
     * @param array<string, mixed> $options
     * @return array{RequestInterface, array<string, mixed>}
     */
    public function prepareRequest(RequestInterface $request, array $options = []): array;
}
