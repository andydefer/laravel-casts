<?php

declare(strict_types=1);

namespace AndyDefer\LaravelEloquentCasts;

use Illuminate\Support\ServiceProvider;

final class LaravelEloquentCastsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No registration needed for casts
    }

    public function boot(): void
    {
        // No bootstrapping needed for casts
    }
}
