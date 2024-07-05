<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\PaymentAccount;
use Livewire\Component;
use Livewire\WithPagination;

class Accountbook extends Component
{
    use WithPagination;

    public $current_balance;
    public $activeTab = "all";
    public $record;
    public $from_date;
    public $to_date;

    public int | string $perPage = 10;

    public function getTitle() : string {
        return "";
    }


    public function updatefromdate()
    {
        
    }

    
    public function clearfilter()
    {
        $this->from_date = "";
        $this->to_date = "";
    }

    public function setactiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function mount($record) {
        $this->current_balance = PaymentAccount::where('id',$record)->first()->current_balance;
    }
    
    
    public function render()
    {
        $accountdetails = Payment::query()
        ->where('account_id',$this->record)
        ->when($this->activeTab == "credit",function($query){
            return $query->where('payment_type','credit');
        })
        ->when($this->activeTab == "debit",function($query){
            return $query->where('payment_type','debit');
        })
        ->when($this->activeTab == "all",function($query){
            return $query->where('payment_type','debit')
            ->orWhere('payment_type','credit');
        })
        ->when($this->from_date && $this->to_date,function($query) {
            return $query->whereBetween('created_at',[$this->from_date,$this->to_date]);
        })
        ->paginate($this->perPage);
        return view('livewire.accountbook',compact('accountdetails'));
    }
}
