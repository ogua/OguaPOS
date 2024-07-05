<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        if ($data['reference_no'] == "") {
            
            $maxcode = DB::table('stock_adjustments')
            ->where('reference_no','like','%SA%')
            ->latest()
            ->first();
            
            if ($maxcode) {
                $max = substr($maxcode->reference_no, 7);
                $number = $max + 1;

                if (strlen($number) === 1) {
                  $code = "SA".date('Y')."/000".$number;
                }elseif (strlen($number) === 2) {
                   $code = "SA".date('Y')."/00".$number;
                }elseif (strlen($number) === 3) {
                   $code = "SA".date('Y')."/0".$number;
                }else{
                    $code = "SA".date('Y')."/".$number;
                }
                
            }else{
                $code = "SA".date('Y')."/0001";
            }

            $data['reference_no'] = $code;
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $records = $this->getRecord();
        
        foreach ($records->stockitems as $stock) {
            
            $producttype = $stock->product->product_type;
            $productid = $stock->product_id;
            $variantid = $stock->variant_id;
            $qty = $stock->qty;
            
            if ($producttype == "Single") {
                
                $currentstock = Product_Warehouse_Inventory::where('product_id',$productid)
                ->first();
                
                $currentstock->qty-=$qty;
                $currentstock->save();
                
            }elseif ($producttype == "Variation") {
                
                $currentstock = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('variant_id',$variantid)
                ->first();
                
                $currentstock->qty-=$qty;
                $currentstock->save();
            }
            
            //update stock history
            $stockhistory = [
                'product_id' => $productid,
                'warehouse_id' => $records->warehouse_id,
                'variant_id' => $variantid,
                'adjustment_item_id' => $stock->id,
                'type' => 'Stock Adjustment',
                'qty_change' => "-".$qty,
                'new_quantity' => $currentstock->qty,
                'date' => now(),
                'reference' => $records->reference_no,
            ];
            
            Stock_History::create($stockhistory);
        }
        
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
