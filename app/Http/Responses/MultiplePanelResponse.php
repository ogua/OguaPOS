<?php

namespace App\Http\Responses;

use App\Filament\Customer\Resources\MyOrdersResource;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\OrderResource;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
 
class MultiplePanelResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // You can use the Filament facade to get the current panel and check the ID
        if (Filament::getCurrentPanel()->getId() === 'admin') {
           // return redirect()->to(Dashboard::getUrl('index'));
        }
 
        if (Filament::getCurrentPanel()->getId() === 'cashie') {
           // return redirect()->to(MyOrdersResource::getUrl('index'));
        }

        // RegistrationResponse is like this if some one need to redirect user to specific page after register.

        // Just change LoginResponse to RegistrationResponse
 
        return parent::toResponse($request);
    }
}