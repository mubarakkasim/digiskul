<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Configure tenancy routes
        $this->configureTenancyRoutes();
    }

    protected function configureTenancyRoutes(): void
    {
        Route::middleware([
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ])->group(base_path('routes/web.php'));

        Route::middleware([
            'api',
            InitializeTenancyByDomain::class,
        ])->prefix('api')->group(base_path('routes/api.php'));
    }
}

