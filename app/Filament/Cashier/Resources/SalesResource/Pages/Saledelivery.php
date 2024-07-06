<?php

namespace App\Filament\Cashier\Resources\SalesResource\Pages;

use App\Filament\Cashier\Resources\SalesResource;
use App\Models\Delivery;
use App\Models\DeliveryHistory;
use App\Models\Sales;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

class Saledelivery extends Page
{
    use WithFileUploads;

    protected static string $resource = SalesResource::class;

    protected static string $view = 'filament.resources.sales-resource.pages.saledelivery';

    public $data;

    #[Validate('required')]
    public $shipping_details;
    
    public $shipping_address;

    #[Validate('required')]
    public $shipping_status;

    public $delivered_to;
    public $shipping_note;

    #[Validate('required')]
    public $expected;

    public $deliveredon;

    #[Validate('image|max:1024|nullable')] // 1MB Max
    public $shipping_file;

    public $delivery;

    public function getTitle(): string 
    {
        return "Sales Delivery - ".$this->data->reference_number;
    }

    public function mount($record) {

        $sale = Sales::with('delivery')->where('id',$record)
        ->first();

        $this->data = $sale;

        $delivery =  Delivery::where('sale_id',$sale->id)->first();

        $this->delivery = $delivery;

        $this->shipping_details = $delivery->shipping_detail ?? "";
        $this->shipping_address = $delivery->shipping_address ?? "";
        $this->shipping_status = $delivery->shipping_status ?? "";
        $this->delivered_to = $delivery->delivered_to ?? "";
        $this->shipping_note = $delivery->shipping_note ?? "";
        $this->expected = $delivery->expected ?? "";
        $this->deliveredon = $delivery->deliveredon ?? "";

    }

    public function saveshipping() {

         $this->validate();
        
        $delierydata = [
            'sale_id' => $this->data->id,
            'shipping_detail' => $this->shipping_details,
            'shipping_address' => $this->shipping_address,
            'shipping_status' => $this->shipping_status,
            'delivered_to' => $this->delivered_to,
            'shipping_note' => $this->shipping_note,
            'expected' => $this->expected,
            'deliveredon' => $this->deliveredon,
            'shipping_documents' => $this->shipping_file ? $this->shipping_file->store() : '',
        ];

        $delivery =  Delivery::where('sale_id',$this->data->id)->first();

        if ($delivery) {

           $delivery->shipping_detail = $this->shipping_details;
           $delivery->shipping_address = $this->shipping_address;
           $delivery->shipping_status = $this->shipping_status;
           $delivery->delivered_to = $this->delivered_to;
           $delivery->shipping_note = $this->shipping_note;

           if($this->expected){
                $delivery->expected = $this->expected ?? null;
           }

           if($this->deliveredon){
                $delivery->deliveredon = $this->deliveredon ?? null;
           }
           
           $delivery->shipping_documents = $this->shipping_file ? $this->shipping_file->store() : '';
           $delivery->save();

        }else {

           $delivery = new Delivery($delierydata);
           $delivery->save();
        }

        //check previous delivery
        $check = DeliveryHistory::where('delivery_id',$delivery->id)
        ->latest()->first();


        $delieryhistory = [
            'delivery_id' => $delivery->id,
            'date' => date('Y-m-d'),
            'edited_by' => auth()->user()->id,
            'note' => $this->shipping_note,
            'from_statues' => $check?->from_statues ? $check->from_statues : $this->shipping_status,
            'to_statues' => $this->shipping_status ?? "Ordered",
        ];

        $deliveryh = new DeliveryHistory($delieryhistory);
        $deliveryh->save();

        Notification::make()
        ->title('Recorded successfully!')
        ->success()
        ->body('Delivery recorded successfully!.')        
        ->send();
    }
    

}
