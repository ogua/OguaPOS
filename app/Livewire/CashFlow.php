<?php

namespace App\Livewire;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class CashFlow extends Component
{
    use WithPagination;

    public int | string $perPage = 10;
    
    public function render()
    {
        $accountdetails = Payment::query()
        ->latest()
        ->paginate($this->perPage);

        return view('livewire.cash-flow',compact('accountdetails'));
    }
}
