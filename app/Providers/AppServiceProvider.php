<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Livewire\DatabaseNotifications;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Policies\ActivityPolicy;
use Spatie\Activitylog\Models\Activity;

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
        Gate::define('viewPulse', function (User $user) {
            return $user->usertype === 'dev';
        });

        // Gate::policy(Activity::class, ActivityPolicy::class);
    }
}
