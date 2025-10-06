<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Services;

use dsolodev\Cloudflare\Http\Adapters\GuzzleAdapter;
use dsolodev\Cloudflare\Http\Auth\AuthFactory;
use dsolodev\Cloudflare\Http\Contracts\HttpAdapterInterface;
use dsolodev\Cloudflare\Http\Debug;
use dsolodev\Cloudflare\Http\Exceptions\ApiResponseException;
use dsolodev\Cloudflare\Http\Exceptions\AuthException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

final readonly class CloudflareService
{
    public string                $email;
    private HttpAdapterInterface $client;
    private string               $token;

    /**
     * @throws AuthException
     */
    public function __construct(string $email = '', string $token = '', ?HttpAdapterInterface $client = null, string $authStrategy = 'Bearer') {
        if ($token === '' || $token === '0' || ($email === '' || $email === '0')) {
            throw new InvalidArgumentException('Both email and token are required for Cloudflare API access.');
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format provided.');
        }

        // Basic token validation (should be non-empty and reasonable length)
        if (mb_strlen($token) < 10) {
            throw new InvalidArgumentException('Token appears to be invalid (too short).');
        }

        $this->email = $email;
        $this->token = $token;

        $auth         = AuthFactory::create($authStrategy, [
            'token'  => $this->token,
            'email'  => $this->email,
            'apiKey' => $this->token,
        ]);
        $this->client = $client ?? new GuzzleAdapter($auth);
    }

    /**
     * @param array<string, mixed> $queryParams
     *
     * @throws GuzzleException
     * @throws ApiResponseException
     */
    public function get(string $endpoint, array $queryParams = []): mixed {
        $response = $this->client->get($endpoint, $queryParams);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws ApiResponseException
     * @throws GuzzleException
     */
    public function post(string $endpoint, array $data = [], array $options = []): mixed {
        $response = $this->client->post($endpoint, $data, $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws ApiResponseException
     * @throws GuzzleException
     */
    public function patch(string $endpoint, array $data = [], array $options = []): mixed {
        $response = $this->client->patch($endpoint, $data, $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws ApiResponseException
     * @throws GuzzleException
     */
    public function put(string $endpoint, array $data = [], array $options = []): mixed {
        $response = $this->client->put($endpoint, $data, $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws ApiResponseException
     * @throws GuzzleException
     */
    public function delete(string $endpoint, array $data = [], array $options = []): mixed {
        $response = $this->client->delete($endpoint, $data, $options);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getDebug(): Debug {
        return $this->client->getDebug();
    }
}
