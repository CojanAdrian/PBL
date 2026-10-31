<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\Dashboard;
use App\Filament\Support\Palette;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Support\Colors\Color;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('CA.web')
            ->brandLogo(asset('images/calogo-dark.svg'))
            ->darkModeBrandLogo(asset('images/calogo.png'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/favicon.svg'))
            ->colors([
                'primary' => Palette::SYSTEM_BLUE,
                'gray' => Color::Slate,
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->font('Manrope', url: 'https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap', provider: GoogleFontProvider::class)
            ->spa()
            ->unsavedChangesAlerts()
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->databaseTransactions()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('17rem')
            ->collapsedSidebarWidth('6rem')
            ->maxContentWidth(Width::Full)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('Website'),
                NavigationGroup::make('Legal & Help'),
                NavigationGroup::make('System')->collapsed(),
            ])
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): string => Blade::render(
                    '<footer class="py-6 text-center text-[11px] text-gray-400 dark:text-gray-500">CA.web</footer>'
                ),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
            ]);
    }
}
