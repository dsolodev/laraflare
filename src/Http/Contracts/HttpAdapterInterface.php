<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Contracts;

use dsolodev\Cloudflare\Http\Debug;
use Psr\Http\Message\ResponseInterface;

interface HttpAdapterInterface
{
    /**
     * @param array<string, mixed> $queryParams
     *
     */
    public function get(string $endpoint, array $queryParams = []): ResponseInterface;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     */
    public function post(string $endpoint, array $data = [], array $options = []): ResponseInterface;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     */
    public function patch(string $endpoint, array $data = [], array $options = []): ResponseInterface;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     */
    public function delete(string $endpoint, array $data = [], array $options = []): ResponseInterface;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     */
    public function put(string $endpoint, array $data = [], array $options = []): ResponseInterface;

    public function getDebug(): Debug;
}
