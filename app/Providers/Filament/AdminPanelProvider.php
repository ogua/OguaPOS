<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use App\Models\Warehouse;
use Filament\PanelProvider;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\SalesResource;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Pages\Tenancy\RegisterWarehouse;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->profile(EditProfile::class)
        ->login(Login::class)
        ->navigation(request()->routeIs('filament.admin.resources.sales.pos.create') ? false : true)
        ->passwordReset()
        ->unsavedChangesAlerts()
        ->sidebarCollapsibleOnDesktop()
        ->brandLogo(asset('images/logo.png'))
        //->brandLogoHeight(300)
        ->brandLogoHeight('4rem')
        ->favicon(asset('images/logo.png'))
        ->navigationGroups([
            'Products',
            'Sale',
            'Purchases',
            'Stock Management',
            'Payment Accounts',
            'People',
            'Settings'
            ])
        ->navigationItems([
            NavigationItem::make('paymentaccount')
                ->label('Payment Account Report')
                ->url(fn (): string => SalesResource::getUrl('payment-account-report'))
                ->icon('heroicon-o-banknotes')
                ->group('Payment Accounts')
                ->hidden()
                ->sort(3)
        ])
            ->colors([
                'primary' => Color::Amber,
                ])
                ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
                ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
                ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
                ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
                ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
                ->pages([
                    // Dashboard::class
                     Dashboard::class
                    ])
                    ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
                    ->widgets([
                        Widgets\AccountWidget::class,
                        // Widgets\FilamentInfoWidget::class,
                        ])
                        ->plugins([
                            FilamentApexChartsPlugin::make(),
                            FilamentBackgroundsPlugin::make()
                                ->imageProvider(
                                    MyImages::make()
                                        ->directory('images/backgrounds')
                                ),
                            ])
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
