<?php

namespace App\Providers;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\View\View;

class SalesPosProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            fn (): View => view('pos-button'),
        );

        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        //     fn (): View => view('livewire.calculator'),
        // );

        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        //     fn (): View => view('dashbaord'),
        // );

        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): View => view('livewire.footer-sound'),
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
