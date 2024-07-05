<?php

namespace App\Livewire;

use App\Models\Product_variation;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Livewire\Component;
use Filament\Notifications\Notification;

class PriceGroup extends Component
{
    
    public $data;
    public $inventoryData = [];
    
    public function mount($record) {
        $this->data = $record;
        $this->initializeInventoryData();
    }
    
    public function initializeInventoryData()
    {
        foreach ($this->data->warehouses as $warehouse) {
            $inventory = Product_Warehouse_Inventory::where('product_id', $warehouse->pivot->product_id)
            ->where('warehouse_id', $warehouse->pivot->warehouse_id)->get();
            
            foreach ($inventory as $row) {
                $this->inventoryData[$row->id] = [
                    'price' => $row->cost_price,
                    'wholesale' => $row->wholesale_price,
                ];
            }
        }
    }
    
    
    public function saveopeningstock() {
        
        $data = $this->inventoryData;
        
        foreach ($data as $key => $row) {
            
            $warehouse = Product_Warehouse_Inventory::where('id',$key)->first();
            $warehouse->selling_price = $row['price'];
            $warehouse->wholesale_price = $row['wholesale'];
            $warehouse->save();
            
            Product_variation::where('id',$warehouse->variant_id)->update(['selling_price' => $row['price']]);    
        }

        //send notification message
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        $this->dispatch("success-sound");
    }
    
    
    public function render()
    {
        return view('livewire.price-group');
    }
}
