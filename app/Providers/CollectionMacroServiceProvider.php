<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CollectionMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Collection::macro('toStringLength', function () {
            return $this->map(function (string $value) {
                return Str::length($value);
            });
        });

        Collection::macro('increment', function ($number = 1) {
            return $this->map(function (int $value) use ($number){
                return $value + $number;
            });
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
