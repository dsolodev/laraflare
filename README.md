# Laraflare - Cloudflare API Client for Laravel

A modern, type-safe Cloudflare API v4 client for Laravel applications.

## Features

- ✅ Full support for Cloudflare API v4
- ✅ Multiple authentication strategies (Bearer Token & API Key)
- ✅ Type-safe with PHPStan Level 9
- ✅ PSR-7 HTTP message implementation
- ✅ Automatic JSON response decoding
- ✅ Debug mode for request/response inspection
- ✅ Mock service for testing
- ✅ Laravel auto-discovery support

## Requirements

- PHP 8.4 or higher
- Laravel 11.0 or higher
- Guzzle HTTP 7.0 or higher

## Installation

Install via Composer:

```bash
composer require dsolodev/laraflare
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="dsolodev\Cloudflare\Providers\CloudflareServiceProvider"
```

This will create a `config/cloudflare.php` configuration file.

## Configuration

### Environment Variables

#### Bearer Token Authentication (Recommended)

Modern API tokens provide scoped access and better security:

```env
# Required: Your Cloudflare account email
CLOUDFLARE_USERNAME=your-email@example.com

# Required: Your Cloudflare API token
CLOUDFLARE_TOKEN=your-api-token-here

# Optional: Authentication strategy (defaults to Bearer)
CLOUDFLARE_AUTH_STRATEGY=Bearer

# Optional: Driver mode (api, log, or null for mock)
CLOUDFLARE_DRIVER=api
```

To create an API token:
1. Go to Cloudflare Dashboard → My Profile → API Tokens
2. Click "Create Token"
3. Select a template or create a custom token with required permissions

#### API Key Authentication (Legacy)

For backward compatibility with legacy Global API keys:

```env
# Required: Your Cloudflare account email
CLOUDFLARE_USERNAME=your-email@example.com

# Required: Your Cloudflare Global API key
CLOUDFLARE_API_KEY=your-global-api-key

# Required: Set auth strategy to ApiKey
CLOUDFLARE_AUTH_STRATEGY=ApiKey

# Optional: Driver mode (api, log, or null for mock)
CLOUDFLARE_DRIVER=api
```

To find your Global API key:
1. Go to Cloudflare Dashboard → My Profile → API Tokens
2. View your "Global API Key"

### Authentication Strategies

The package automatically selects the correct credential based on `CLOUDFLARE_AUTH_STRATEGY`:

- **`Bearer`** (default) - Uses `CLOUDFLARE_TOKEN` for modern API tokens
- **`ApiKey`** - Uses `CLOUDFLARE_API_KEY` for legacy Global API keys

### Driver Modes

- `api` - Normal operation (default)
- `log` - Mock service with logging enabled
- `null` - Mock service without logging

## Usage

### Basic Usage

#### Using Facade

```php
use dsolodev\Cloudflare\Facades\Cloudflare;

// List all zones
$zones = Cloudflare::get('zones');

// Get specific zone
$zone = Cloudflare::get('zones/zone-id');

// Create DNS record
$record = Cloudflare::post('zones/zone-id/dns_records', [
    'type' => 'A',
    'name' => 'example.com',
    'content' => '192.0.2.1',
    'ttl' => 3600,
    'proxied' => true,
]);

// Update DNS record
$updated = Cloudflare::patch('zones/zone-id/dns_records/record-id', [
    'content' => '192.0.2.2',
]);

// Delete DNS record
$result = Cloudflare::delete('zones/zone-id/dns_records/record-id');
```

#### Using Dependency Injection

```php
use dsolodev\Cloudflare\Services\CloudflareService;

class DnsController extends Controller
{
    public function __construct(
        private CloudflareService $cloudflare
    ) {}

    public function index()
    {
        $zones = $this->cloudflare->get('zones');

        return view('dns.index', compact('zones'));
    }

    public function createRecord(Request $request)
    {
        $record = $this->cloudflare->post("zones/{$request->zone_id}/dns_records", [
            'type' => $request->type,
            'name' => $request->name,
            'content' => $request->content,
            'ttl' => $request->ttl ?? 1,
            'proxied' => $request->proxied ?? false,
        ]);

        return response()->json($record);
    }
}
```

### Available Methods

All methods return decoded JSON as PHP arrays:

```php
// GET request
$data = Cloudflare::get(string $endpoint, array $queryParams = []): array

// POST request
$data = Cloudflare::post(string $endpoint, array $data = [], array $options = []): array

// PUT request
$data = Cloudflare::put(string $endpoint, array $data = [], array $options = []): array

// PATCH request
$data = Cloudflare::patch(string $endpoint, array $data = [], array $options = []): array

// DELETE request
$data = Cloudflare::delete(string $endpoint, array $data = [], array $options = []): array
```

### Query Parameters

Pass query parameters as an array:

```php
$zones = Cloudflare::get('zones', [
    'status' => 'active',
    'page' => 1,
    'per_page' => 20,
    'order' => 'name',
    'direction' => 'asc',
]);
```

### Working with Responses

All responses are automatically decoded JSON arrays:

```php
$response = Cloudflare::get('zones');

// Check if request was successful
if ($response['success']) {
    foreach ($response['result'] as $zone) {
        echo $zone['name'] . "\n";
    }
}

// Handle errors
if (!empty($response['errors'])) {
    foreach ($response['errors'] as $error) {
        echo "Error: {$error['message']}\n";
    }
}
```

### Debug Mode

Access debug information for the last request:

```php
use dsolodev\Cloudflare\Facades\Cloudflare;

$zones = Cloudflare::get('zones');

$debug = Cloudflare::getDebug();

// Request details
dump($debug->lastRequestHeaders);
dump($debug->lastRequestBody);

// Response details
dump($debug->lastResponseCode);
dump($debug->lastResponseHeaders);

// Errors
dump($debug->lastResponseError);
```

### Mock Service for Testing

Enable mock mode in your test environment:

```php
// In .env.testing
CLOUDFLARE_DRIVER=log
```

Or programmatically:

```php
use dsolodev\Cloudflare\Services\CloudflareMockService;

$mock = new CloudflareMockService(enableLogging: true);

$result = $mock->get('zones');
// Returns: ['mocked' => true, 'method' => 'get', 'endpoint' => 'zones']
```

## Common Examples

### Zone Management

```php
// List zones
$zones = Cloudflare::get('zones');

// Get zone details
$zone = Cloudflare::get("zones/{$zoneId}");

// Update zone settings
$settings = Cloudflare::patch("zones/{$zoneId}/settings/ssl", [
    'value' => 'flexible',
]);

// Purge cache
$result = Cloudflare::post("zones/{$zoneId}/purge_cache", [
    'purge_everything' => true,
]);
```

### DNS Records

```php
// List DNS records
$records = Cloudflare::get("zones/{$zoneId}/dns_records", [
    'type' => 'A',
    'name' => 'example.com',
]);

// Create A record
$record = Cloudflare::post("zones/{$zoneId}/dns_records", [
    'type' => 'A',
    'name' => 'subdomain.example.com',
    'content' => '192.0.2.1',
    'ttl' => 1,
    'proxied' => true,
]);

// Update DNS record
$updated = Cloudflare::put("zones/{$zoneId}/dns_records/{$recordId}", [
    'type' => 'A',
    'name' => 'subdomain.example.com',
    'content' => '192.0.2.2',
    'ttl' => 1,
    'proxied' => true,
]);

// Delete DNS record
$result = Cloudflare::delete("zones/{$zoneId}/dns_records/{$recordId}");
```

### Firewall Rules

```php
// List firewall rules
$rules = Cloudflare::get("zones/{$zoneId}/firewall/rules");

// Create firewall rule
$rule = Cloudflare::post("zones/{$zoneId}/firewall/rules", [
    'filter' => [
        'expression' => '(http.request.uri.path contains "/api")',
    ],
    'action' => 'block',
    'description' => 'Block API access',
]);
```

### Page Rules

```php
// List page rules
$pageRules = Cloudflare::get("zones/{$zoneId}/pagerules");

// Create page rule
$rule = Cloudflare::post("zones/{$zoneId}/pagerules", [
    'targets' => [
        [
            'target' => 'url',
            'constraint' => [
                'operator' => 'matches',
                'value' => '*example.com/admin/*',
            ],
        ],
    ],
    'actions' => [
        [
            'id' => 'ssl',
            'value' => 'full',
        ],
    ],
    'status' => 'active',
]);
```

## Error Handling

```php
use dsolodev\Cloudflare\Http\Exceptions\ApiResponseException;
use dsolodev\Cloudflare\Http\Exceptions\AuthException;
use GuzzleHttp\Exception\GuzzleException;

try {
    $zones = Cloudflare::get('zones');
} catch (AuthException $e) {
    // Authentication failed
    Log::error('Cloudflare auth error: ' . $e->getMessage());
} catch (ApiResponseException $e) {
    // API returned an error
    Log::error('Cloudflare API error: ' . $e->getMessage());
} catch (GuzzleException $e) {
    // Network or HTTP error
    Log::error('HTTP error: ' . $e->getMessage());
}
```

## Advanced Usage

### Custom HTTP Client

Inject a custom HTTP adapter:

```php
use dsolodev\Cloudflare\Http\Adapters\GuzzleAdapter;
use dsolodev\Cloudflare\Http\Auth\BearerAuth;
use dsolodev\Cloudflare\Services\CloudflareService;

$auth = new BearerAuth('your-token', 'your-email@example.com');
$adapter = new GuzzleAdapter($auth, 'https://api.cloudflare.com/client/v4/');

$service = new CloudflareService(
    email: 'your-email@example.com',
    token: 'your-token',
    client: $adapter
);

$zones = $service->get('zones');
```

### Custom Headers

```php
use dsolodev\Cloudflare\Facades\Cloudflare;

$adapter = app(CloudflareService::class)->getDebug();
// Note: Direct header manipulation requires accessing the underlying adapter
```

## Development

### Code Quality

```bash
# Run linter
composer lint

# Check code style
composer test:lint

# Run static analysis
composer test:types

# Run refactoring checks
composer test:refactor

# Run all tests
composer test
```

### Tools Used

- **Laravel Pint** - Code style formatting
- **PHPStan** - Static analysis (Level 9)
- **Rector** - Automated refactoring

## API Documentation

For complete Cloudflare API documentation, visit:
https://developers.cloudflare.com/api/

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- **Author**: JC (jc@dsolo.dev)
- **Package**: dsolodev/laraflare

## Support

For issues, questions, or contributions, please visit the GitHub repository.
