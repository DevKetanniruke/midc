<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // The admin role passes every permission check.
        Gate::before(fn ($user) => $user->hasRole('admin') ? true : null);

        // Every view gets $site: the name, logo and details saved on the Settings page.
        View::composer('*', fn ($view) => $view->with('site', Setting::site()));
    }
}
