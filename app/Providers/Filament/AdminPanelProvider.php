<?php

namespace App\Providers\Filament;


use Filament\Panel;
use Filament\Widgets;
use App\Models\Company;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Filament\Navigation\NavigationItem;
use App\Http\Middleware\ApplyTenantScopes;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Pages\Tenancy\RegisterTeam;
use Filament\Pages\Tenancy\EditTenantProfile;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->profile()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->emailVerification()
            ->passwordReset()
            ->sidebarCollapsibleOnDesktop()
            ->brandName('Elevate ERP')
            // ->brandLogo(asset('home/assets/img/3-removebg-preview.png'),)
            // ->brandLogoHeight('2rem')
            ->favicon(asset('home/assets/img/2.png'))
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Amber,
                'success' => Color::Green,
                'warning' => Color::Sky,
            ])

            ->navigationGroups([
                'Account',
                'Management Financial',
                'Management SDM',
                'Management CRM',
                'Management Project',
                'Management Sales And Purchasing',
                'Management Stock',
                'Management Supplier',
                'Settings',
                'Reports',
                'User',
            ])

            ->databaseNotifications()

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            ->tenant(Company::class, ownershipRelationship: 'company', slugAttribute: 'slug')
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class)
            // ->tenantDomain('{tenant:slug}.localhost')
            ->tenantMenuItems([
                'profile' => MenuItem::make()
                    ->label('Edit team profile')
                    ->visible(fn(): bool => Auth::user()->usertype === 'member'),
                'register' => MenuItem::make()
                    ->label('Register new team')
                    ->visible(fn(): bool => Auth::user()->usertype === 'member'),
                // MenuItem::make()
                //     ->label('Settings')
                //     ->url(fn (): string => Settings::getUrl())
                //     ->icon('heroicon-m-cog-8-tooth')
                //     ->tenant(),
                // ...
            ])
            ->userMenuItems([
                // MenuItem::make()
                //     ->label('Settings')
                //     ->url(fn (): string => Settings::getUrl())
                //     ->icon('heroicon-m-cog-8-tooth'),
                // ...
            ])
            ->tenantMiddleware([
                ApplyTenantScopes::class,
            ], isPersistent: true);
    }
}
