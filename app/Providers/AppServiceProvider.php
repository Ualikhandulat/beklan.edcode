<?php

namespace App\Providers;

use App\Enums\Role;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
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
        RedirectIfAuthenticated::redirectUsing(function () {
            return match (Auth::user()->role) {
                Role::Admin => route('admin.dashboard'),
                Role::Student => route('student.dashboard'),
            };
        });
    }
}
