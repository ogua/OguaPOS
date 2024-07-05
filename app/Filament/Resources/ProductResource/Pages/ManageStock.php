<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\Product_variation;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ManageStock extends Page
{
    protected static string $resource = ProductResource::class;
    
    protected static string $view = 'filament.resources.product-resource.pages.manage-stock';
    
    public $data;
    public $inventoryData = [];
    
    public function mount($record) {
        $this->data = Product::where('id', $record)->first();
        $this->initializeInventoryData();
    }
    
    public function initializeInventoryData()
    {
        foreach ($this->data->warehouses as $warehouse) {
            $inventory = Product_Warehouse_Inventory::where('product_id', $warehouse->pivot->product_id)
            ->where('warehouse_id', $warehouse->pivot->warehouse_id)->get();
            
            foreach ($inventory as $row) {
                $this->inventoryData[$row->id] = [
                    'qty' => $row->qty,
                    'price' => $row->cost_price,
                    'subtotal' => number_format( (int) $row->cost_price * (int) $row->qty,2),
                ];
            }
        }
    }
    
    public function InventoryData($field, $id)
    {
        if ($field === 'qty' || $field === 'price') {
            $this->inventoryData[$id]['subtotal'] = number_format((int) $this->inventoryData[$id]['qty'] * (int) $this->inventoryData[$id]['price'],2);
        }
    }
    
    
    public function saveopeningstock() {
        
        $data = $this->inventoryData;
        
        foreach ($data as $key => $row) {
            $update = [
                'qty' => $row['qty'],
                'price' => $row['price']
            ];
            
            $warehouse = Product_Warehouse_Inventory::where('id',$key)->first();
            $warehouse->qty = $row['qty'];
            $warehouse->cost_price = $row['price'];
            $warehouse->save();
            
            Product_variation::where('id',$warehouse->variant_id)->update(['cost_price' => $row['price']]);
            
            $stockhistory = [
                'product_id' => $warehouse->product_id,
                'warehouse_id' => $warehouse->warehouse_id,
                'variant_id' => $warehouse->variant_id ?? null,
                'type' => 'Opening stock',
                'qty_change' => "+".$row['qty'],
                'new_quantity' => $row['qty'],
                'date' => now(),
                'reference' => "OPSTCK",
            ];

            $product_type = $warehouse->product->product_type;
            $product_id = $warehouse->product_id;
            $warehouse_id = $warehouse->warehouse_id;
            $variant_id = $warehouse->variant_id ?? null;
            
            if($warehouse->product->product_type == "Single"){
                
                $check = Stock_History::where('product_id', $warehouse->product_id)
                ->where('warehouse_id',$warehouse->warehouse_id)
                ->orderBy('id','desc')
                ->first();
                
                if ($check) {
                    $check->new_quantity = $row['qty'];
                    $check->save();
                    
                }else {
                    Stock_History::create($stockhistory);
                }
                
            }elseif($warehouse->product->product_type == "Variation"){
                
                $check = Stock_History::where('product_id', $warehouse->product_id)
                ->where('warehouse_id',$warehouse->warehouse_id)
                ->where('variant_id',$warehouse->variant_id)
                ->orderBy('id','desc')
                ->first();
                
                if ($check) {
                    $check->new_quantity = $row['qty'];
                    $check->save();
                }else {
                    Stock_History::create($stockhistory);
                }
                
            }else {
                # combo product
            }

            $this->generateStockReport($product_type,$product_id,$warehouse_id,$variant_id,$row['qty']);
            
        }

        //send notification message
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        $this->dispatch("success-sound");
    }
    
    
    private function generateStockReport($product_type,$product_id,$warehouse_id,$variant_id,$newqty)
    {
        
        if($product_type == "Single"){
            
            $stockHistories = Stock_History::where('product_id', $product_id)
            ->where('warehouse_id',$warehouse_id)
            ->orderBy('id','desc')
            ->get();
            
            
        }elseif($product_type == "Variation"){
            
            $stockHistories = Stock_History::where('product_id', $product_id)
            ->where('warehouse_id',$warehouse_id)
            ->where('variant_id',$variant_id)
            ->orderBy('id','desc')
            ->get();
            
        }else {
            $stockHistories = [];
        }
        
        $currentStock = $newqty;

        $count = 0;
        
        foreach ($stockHistories as $history) {

            if ($count > 0) {
                 $history->new_quantity = $currentStock;
                 $history->save();
            }


            if ($history->qty_change < 0) {
                $currentStock += abs($history->qty_change);
            } else {
                $currentStock -= substr($history->qty_change,1);
            }

            if ($history->first()) {
                $count ++;
                continue;
            }
            
            //$history->new_quantity = $currentStock;
            //$history->save();

            $count ++;
        }
        
    }
    
}
