<?php

namespace App\Providers\Filament;

use BladeUI\Icons\Components\Icon;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Routing\Route;

class DevPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dev')
            ->path('dev')
            ->login()
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('Elevate ERP')
            ->brandLogo(asset('home/assets/img/1-removebg.png'),)
            ->brandLogoHeight('2rem')
            ->favicon(asset('home/assets/img/2.png'))
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => 'rgb(52, 80, 124)',
                'success' => Color::Green,
                'warning' => Color::Yellow,
            ])
            ->discoverResources(in: app_path('Filament/Dev/Resources'), for: 'App\\Filament\\Dev\\Resources')
            ->discoverPages(in: app_path('Filament/Dev/Pages'), for: 'App\\Filament\\Dev\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Dev/Widgets'), for: 'App\\Filament\\Dev\\Widgets')
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('API')
                    ->icon('fileicon-api-blueprint')
                    ->url('docs/api'),
            ])
            ->plugins([
                \Hasnayeen\Themes\ThemesPlugin::make()
            ]);
    }
}
