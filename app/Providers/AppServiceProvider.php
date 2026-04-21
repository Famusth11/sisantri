<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    


    public function register(): void
    {
    }
    
    


    public function boot(): void
    {
        // Keep boot side-effect free so serverless requests do not fail
        // before a route actually needs the database.
    }
}
