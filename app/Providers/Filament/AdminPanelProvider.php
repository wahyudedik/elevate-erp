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
            ->id('admin')
            ->path('admin')
            ->login()
            // ->registration()
            ->emailVerification()
            ->passwordReset()
            ->profile()
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('Elevate ERP')
            ->brandLogo(asset('home/assets/img/1-removebg.png'),)
            ->brandLogoHeight('2rem')
            ->favicon(asset('home/assets/img/2.png'))
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Amber,
                // 'primary' => '#34507c',
                'primary' => 'rgb(52, 80, 124)',
                'success' => Color::Green,
                'warning' => Color::Yellow,
            ])
            ->navigationGroups([
                'Master Data',
                'Management Financial',
                'Management SDM',
                'Management CRM',
                'Management Project',
                'Management Sales And Purchasing',
                'Management Stock',
                'Management Supplier',
                'Management Users',
                'Reports',
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
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
            ->tenantMenuItems([
                'profile' => MenuItem::make()
                    ->label('Edit Company profile')
                    ->visible(fn(): bool => Auth::user()->usertype === 'member' || Auth::user()->usertype === 'dev'),
                'register' => MenuItem::make()
                    ->label('Register new Company')
                    ->visible(fn(): bool => Auth::user()->usertype === 'member' || Auth::user()->usertype === 'dev'),
                // MenuItem::make()
                //     ->label('Settings')
                //     ->url(fn (): string => Settings::getUrl())
                //     ->icon('heroicon-m-cog-8-tooth')
                //     ->tenant(),
                // ...
            ])
            ->userMenuItems([
                // MenuItem::make()
                //     ->label('Webchat')
                //     ->url(fn (): string => Settings::getUrl())
                //     ->icon('heroicon-m-chat-bubble-left-right'),
                // ...
                // MenuItem::make()
                //     ->label('Webcam')
                //     ->url(fn (): string => Settings::getUrl())
                //     ->icon('heroicon-m-video-camera'),
                // ...
                // MenuItem::make()
                //     ->label('Wallet')
                //     ->url(fn (): string => Settings::getUrl())
                //     ->icon('heroicon-m-wallet'),
                // ...
            ])
            ->tenantMiddleware([
                ApplyTenantScopes::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
            ], isPersistent: true)
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \Hasnayeen\Themes\ThemesPlugin::make()
                // ->canViewThemesPage(fn () => Auth::user()->usertype === 'dev'),
            ]);
    }
}
