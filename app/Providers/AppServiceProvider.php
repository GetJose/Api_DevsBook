<?php

namespace App\Providers;

use Illuminate\Foundation\Console\VendorPublishCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
      //  Intervention/Image/ImageServiceProvider::class;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
    }
}
