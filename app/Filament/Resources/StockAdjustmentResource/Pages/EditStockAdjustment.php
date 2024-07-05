<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockAdjustment extends EditRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $records = $this->getRecord();
        
        foreach ($records->stockitems as $key => $stock) {
            
            $producttype = $stock->product->product_type;
            $productid = $stock->product_id;
            $variantid = $stock->variant_id;
            $qty = $stock->qty;
            
            if ($producttype == "Single") {
                
                $currentstock = Stock_History::where('product_id',$productid)
                ->where('adjustment_item_id',$stock->id)
                ->where('warehouse_id',$records->warehouse_id)
                ->first();
                
                $currentstock->qty_change = "-".$qty;
                $currentstock->save();
                
            }elseif ($producttype == "Variation") {
                
                $currentstock = Stock_History::where('product_id',$productid)
                ->where('variant_id',$variantid)
                ->where('warehouse_id',$records->warehouse_id)
                ->where('adjustment_item_id',$stock->id)
                ->first();
                
                $currentstock->qty_change = "-".$qty;
                $currentstock->save();
            }

            $this->generateStockReport($producttype,$productid,$records->warehouse_id,$variantid);
        }
        
    }


    private function generateStockReport($product_type,$product_id,$warehouse_id,$variant_id)
    {
        
        if($product_type == "Single"){
            
            $stockHistories = Stock_History::where('product_id', $product_id)
            ->where('warehouse_id',$warehouse_id)
            ->get();
            
            
        }elseif($product_type == "Variation"){
            
            $stockHistories = Stock_History::where('product_id', $product_id)
            ->where('warehouse_id',$warehouse_id)
            ->where('variant_id',$variant_id)
            ->get();
            
        }else {
            $stockHistories = [];
        }
        
        $currentStock = 0;

        $count = 0;
        
        foreach ($stockHistories as $history) {

            if ($count === 0) {
                $currentStock+=$history->new_quantity;
                $count++;
                continue;
            }


            if ($history->qty_change < 0) {
                $currentStock -= abs($history->qty_change);
            } else {
                $currentStock += substr($history->qty_change,1);
            }

            if ($count > 0) {
                $history->new_quantity = $currentStock;
                $history->save();
            }

            if ($count === count($stockHistories) - 1) {

                if($product_type == "Single"){
                
                    $currentstock = Product_Warehouse_Inventory::where('product_id',$product_id)
                    ->first();
                    
                }elseif($product_type == "Variation"){

                    $currentstock = Product_Warehouse_Inventory::where('product_id',$product_id)
                    ->where('variant_id',$variant_id)
                    ->first();
                    
                }else {
                    $currentstock = null;
                }

                $currentstock->qty = $currentStock;
                $currentstock->save();
                
            }

            $count ++;
        }
        
    }

}
