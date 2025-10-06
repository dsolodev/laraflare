<?php

declare(strict_types=1);

namespace dsolodev\Cloudflare\Facades;

use Illuminate\Support\Facades\Facade;

final class Cloudflare extends Facade
{
    protected static function getFacadeAccessor(): string {
        return 'Cloudflare';
    }
}
