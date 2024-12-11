<?php

namespace App\Providers;

use App\Models\ManagementFinancial\Accounting;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\ManagementFinancial\AccountingPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\ActivityLogger;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Filament\Notifications\Livewire\DatabaseNotifications;

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
        Gate::policy(Accounting::class, AccountingPolicy::class);
    }
}
