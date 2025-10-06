<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Providers;

use dsolodev\Cloudflare\Services\CloudflareMockService;
use dsolodev\Cloudflare\Services\CloudflareService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class CloudflareServiceProvider extends ServiceProvider
{
    public function register(): void {
        $name = 'Cloudflare Laravel SDK';

        $configPath = __DIR__ . '/../../config/cloudflare.php';

        $this->mergeConfigFrom($configPath, 'cloudflare');

        $this->publishes([
            $configPath => config_path('cloudflare.php'),
        ], $name);
    }

    public function boot(): void {
        $this->app->bind('Cloudflare', function (): CloudflareMockService|CloudflareService {
            $driver = config('cloudflare.driver', 'api');

            if (is_null($driver) || $driver === 'log') {
                return new CloudflareMockService($driver === 'log');
            }

            $email = config('cloudflare.username');
            $authStrategy = config('cloudflare.auth_strategy', 'Bearer');

            // Use api_key for ApiKey strategy, api_token for Bearer strategy
            $token = $authStrategy === 'ApiKey'
                ? config('cloudflare.api_key')
                : config('cloudflare.api_token');

            if (empty($email) || empty($token)) {
                $tokenVar = $authStrategy === 'ApiKey' ? 'CLOUDFLARE_API_KEY' : 'CLOUDFLARE_TOKEN';
                throw new InvalidArgumentException(
                    "Cloudflare credentials not configured. Please set CLOUDFLARE_USERNAME and {$tokenVar} environment variables."
                );
            }

            return new CloudflareService(
                email: is_string($email) ? $email : '',
                token: is_string($token) ? $token : '',
                authStrategy: is_string($authStrategy) ? $authStrategy : 'Bearer'
            );
        });
    }
}
