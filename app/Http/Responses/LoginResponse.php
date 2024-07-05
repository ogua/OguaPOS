<?php

namespace App\Http\Responses;

use App\Filament\Resources\SalesResource;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Facades\Filament;

 
class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        // You can use the Filament facade to get the current panel and check the ID
        if (Filament::getCurrentPanel()->getId() === 'admin') {
            return redirect()->to(SalesResource::getUrl('index'));
        }
 
        if (Filament::getCurrentPanel()->getId() === 'cashier') {
            return redirect()->to("/pos");
        }
 
        return parent::toResponse($request);
        
    }
}