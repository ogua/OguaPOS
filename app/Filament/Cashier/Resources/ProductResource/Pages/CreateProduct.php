<?php

namespace App\Filament\Cashier\Resources\ProductResource\Pages;

use App\Filament\Cashier\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product_variation;
use App\Models\Product_Warehouse;
use App\Models\Product_Warehouse_Inventory;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['biller_id'] = auth()->user()->id;
        
        //warehouse
        //$data['payment']["customer_id"] = $data['customer_id'];
        
        // logger($data);
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function beforeCreate(): void
    {
        $data = $this->data;
        $variants = $this->data['variationitems'] ?? [];
        
        //$this->halt();
    }
    
    protected function afterCreate(): void
    {
        $records = $this->getRecord();
        $data = $this->data;
        
        $warehouses = $this->data['warehouses'] ?? [];
        
        $productid = $records->id;
        $product_type = $records->product_type;
        
        $items = $records->variationitems ?? [];
        
        $pivotData = [];
        
        if ($product_type == "Variation") {
            
            foreach ($items as $key => $variant) {
                
                foreach ($data['warehouses'] as $key => $value) {
                    
                    $pivotData = [
                        'cost_price' => $variant->cost_price,
                        'selling_price' => $variant->selling_price,
                        'variant_id' => $variant->id,
                        'wholesale_price' => 0,
                        'warehouse_id' => $value,
                        'product_id' => $records->id
                    ];
                    
                    Product_Warehouse_Inventory::create( $pivotData);
                }
            }
            
        }elseif ($product_type == "Single") {
            
            foreach ($warehouses as $key => $value) {

                $pivotData = [
                    'cost_price' => $data['product_cost'],
                    'selling_price' => $data['product_price'],
                    'variant_id' => null,
                    'wholesale_price' => 0,
                    'warehouse_id' => $value,
                    'product_id' => $records->id
                ];
                
                Product_Warehouse_Inventory::create( $pivotData);                
            }
        }
        
        
    }
    
    
}
