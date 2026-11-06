<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Support\Asset;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panel kandungan untuk penulis, penyemak dan admin: /panel
 */
class AdminPanelProvider extends PanelProvider
{
    /** Kelabu Filament diganti dengan pasir (terang) → biru malam (gelap). */
    private const NIGHT = [
        50 => 'oklch(0.975 0.012 85)',
        100 => 'oklch(0.94 0.02 82)',
        200 => 'oklch(0.88 0.03 80)',
        300 => 'oklch(0.80 0.035 78)',
        400 => 'oklch(0.68 0.035 75)',
        500 => 'oklch(0.55 0.03 260)',
        600 => 'oklch(0.44 0.04 262)',
        700 => 'oklch(0.35 0.045 264)',
        800 => 'oklch(0.27 0.045 265)',
        900 => 'oklch(0.21 0.045 266)',
        950 => 'oklch(0.16 0.04 266)',
    ];

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('panel')
            ->path('panel')
            ->login()
            ->brandName('Jejak Sirah — Panel Kandungan')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.4rem')
            ->favicon(Asset::url('favicon.svg'))
            // Tema sama dengan paparan pemain: malam padang pasir + emas.
            ->darkMode(true, isForced: true)
            ->colors([
                'primary' => Color::hex('#e0a24a'),
                'gray' => self::NIGHT,
                'success' => Color::hex('#4fbf80'),
                'warning' => Color::hex('#f2b84e'),
                'danger' => Color::hex('#e5625a'),
                'info' => Color::hex('#7aa2e0'),
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => new HtmlString(
                '<link rel="stylesheet" href="'.e(Asset::url('css/panel.css')).'">'
            ))
            ->renderHook(PanelsRenderHook::BODY_START, fn () => view('filament.night-sky'))
            ->navigationGroups([
                'Kandungan',
                'Semakan',
                'Rujukan',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
