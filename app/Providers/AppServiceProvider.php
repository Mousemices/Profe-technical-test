<?php

namespace App\Providers;

use App\Services\Internal\Competition\Football\FootballCompetitionService;
use App\Services\Internal\Competition\Football\Generator\GeneratorContract;
use App\Services\Internal\Competition\Football\Generator\TxtFileGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(FootballCompetitionService::class)
            ->needs(GeneratorContract::class)
            ->give(TxtFileGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
