<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\Authenticate;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Authenticate::redirectUsing(function ($request) {
            return $request->expectsJson() ? null : '/';
        });
    }
}
