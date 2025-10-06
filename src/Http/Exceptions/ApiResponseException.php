<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Http\Exceptions;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

use function json_decode;
use function json_encode;

final class ApiResponseException extends CloudflareException
{
    /** @var array<string, mixed> */
    private array $errors = [];

    public function __construct(RequestException $exception) {
        $message = $exception->getMessage();

        if ($exception instanceof ClientException && $exception->hasResponse()) {
            $response     = $exception->getResponse();
            $responseBody = $response->getBody()->getContents();
            $decoded      = json_decode($responseBody, true);
            /** @var array<string, mixed> $errors */
            $errors = is_array($decoded) ? $decoded : ['error' => $responseBody];
            $this->errors = $errors;
            $message      .= ' [details] ' . json_encode($this->errors);
        } elseif ($exception instanceof ServerException) {
            $message .= ' [details] Cloudflare may be experiencing issues or undergoing maintenance. Please try again later.';
        } elseif (!$exception->hasResponse()) {
            $request = $exception->getRequest();

            $message .= ' [url] ' . $request->getUri();
            $message .= ' [method] ' . $request->getMethod();
            $message .= ' [body] ' . $request->getBody()->getContents();
        }

        parent::__construct($message, $exception->getCode(), $exception);
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array {
        return $this->errors;
    }

}
