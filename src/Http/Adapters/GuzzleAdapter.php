<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Adapters;

use dsolodev\Cloudflare\Http\Contracts\AuthInterface;
use dsolodev\Cloudflare\Http\Contracts\HttpAdapterInterface;
use dsolodev\Cloudflare\Http\Debug;
use dsolodev\Cloudflare\Http\Exceptions\ApiResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;

final class GuzzleAdapter implements HttpAdapterInterface
{
    /** @var array<string, string> */
    private array $headers = [];

    public function __construct(
        private readonly AuthInterface $auth,
        private readonly string        $baseUrl = 'https://api.cloudflare.com/client/v4/',
        private ?Client                $guzzle = null,
        private readonly Debug         $debug = new Debug
    ) {
        if (!$this->guzzle instanceof Client) {
            $this->guzzle = new Client([
                'timeout'         => 30,
                'connect_timeout' => 10
            ]);
        }
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     * @throws ApiResponseException
     */
    public function get(string $endpoint, array $queryParams = [], array $options = []): ResponseInterface {
        return $this->makeRequest('GET', $endpoint, [], $queryParams, $options);
    }

    /** @return array<string, string> */
    public function getHeaders(): array {
        return $this->headers;
    }

    public function setHeaders(string $key, string $value): self {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     * @throws ApiResponseException
     */
    public function post(string $endpoint, array $data = [], array $options = []): ResponseInterface {
        return $this->makeRequest('POST', $endpoint, $data, [], $options);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     * @throws ApiResponseException
     */
    public function patch(string $endpoint, array $data = [], array $options = []): ResponseInterface {
        return $this->makeRequest('PATCH', $endpoint, $data, [], $options);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     * @throws ApiResponseException
     */
    public function delete(string $endpoint, array $data = [], array $options = []): ResponseInterface {
        return $this->makeRequest('DELETE', $endpoint, $data, [], $options);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @throws GuzzleException
     * @throws ApiResponseException
     */
    public function put(string $endpoint, array $data = [], array $options = []): ResponseInterface {
        return $this->makeRequest('PUT', $endpoint, $data, [], $options);
    }

    public function getDebug(): Debug {
        return $this->debug;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $options
     *
     * @throws ApiResponseException
     * @throws GuzzleException
     */
    private function makeRequest(
        string $method,
        string $endpoint,
        array  $data = [],
        array  $queryParams = [],
        array  $options = []
    ): ResponseInterface {
        $url = $this->baseUrl . mb_ltrim($endpoint, '/');

        /** @var array<string, string> $headers */
        $headers = array_merge([
            'Accept'       => 'application/json',
            'Content-Type' => $options['contentType'] ?? 'application/json',
        ], $this->headers);

        $request = new Request($method, $url, $headers);
        [$request, $requestOptions] = $this->prepareRequestBody($request, $data, $options);
        $request = $this->addQueryParams($request, $queryParams);

        $response  = null;
        $exception = null;

        try {
            [$request, $requestOptions] = $this->auth->prepareRequest($request, $requestOptions);
            $response = $this->guzzle?->send($request, $requestOptions);

            if (!$response instanceof ResponseInterface) {
                throw new RequestException('No response received from Guzzle client', $request);
            }
        } catch (RequestException $e) {
            $exception = $e;
            throw new ApiResponseException(RequestException::create($e->getRequest(), $e->getResponse(), $e));
        } finally {
            $this->logRequestDebug($request, $response, $exception);
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     *
     * @return array{Request, array<string, mixed>}
     */
    private function prepareRequestBody(Request $request, array $data, array $options): array {
        if (!empty($options['multipart'])) {
            return [
                $request->withoutHeader('Content-Type'),
                ['multipart' => $options['multipart']]
            ];
        }

        if ($data !== []) {
            return [$request->withBody(Utils::streamFor(json_encode($data))), []];
        }

        if (!empty($options['file']) && $options['file'] instanceof StreamInterface) {
            return [$request->withBody($options['file']), []];
        }

        return [$request, []];
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    private function addQueryParams(Request $request, array $queryParams): RequestInterface {
        if ($queryParams === []) {
            return $request;
        }

        $uri           = $request->getUri();
        $existingQuery = [];
        parse_str($uri->getQuery(), $existingQuery);

        return $request->withUri(
            $uri->withQuery(http_build_query(array_merge($existingQuery, $queryParams)))
        );
    }

    private function logRequestDebug(RequestInterface $request, ?ResponseInterface $response, ?Throwable $exception): void {
        $this->debug->lastRequestHeaders  = $this->flattenHeaders($request->getHeaders());
        $this->debug->lastRequestBody     = (string)$request->getBody();
        $this->debug->lastResponseCode    = $response?->getStatusCode();
        $this->debug->lastResponseHeaders = $response instanceof ResponseInterface ? $this->flattenHeaders($response->getHeaders()) : [];
        $this->debug->lastResponseError   = $exception;

        if ($request->getBody()->isSeekable()) {
            $request->getBody()->rewind();
        }
    }

    /**
     * @param array<array<string>> $headers
     *
     * @return array<string>
     */
    private function flattenHeaders(array $headers): array {
        return array_map(fn (array $values): string => implode(', ', $values), $headers);
    }
}
