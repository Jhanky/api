<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Quotation;
use App\Observers\QuotationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer desactivado - la lógica se maneja directamente en el controlador
        // Quotation::observe(QuotationObserver::class);
    }
}
