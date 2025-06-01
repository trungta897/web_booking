<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        Schema::defaultStringLength(191);

        // Add an unread() method to HasMany relationships
        HasMany::macro('unread', function () {
            /** @var \Illuminate\Database\Eloquent\Relations\HasMany $this */
            return $this->getQuery()->where('is_read', false);
        });
    }
}
