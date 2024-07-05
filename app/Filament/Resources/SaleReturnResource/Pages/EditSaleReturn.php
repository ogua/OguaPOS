<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use App\Filament\Resources\SaleReturnResource;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSaleReturn extends EditRecord
{
    protected static string $resource = SaleReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
         $record = $this->getRecord();

        return Notification::make()
            ->success()
            ->title('Return updated')
            ->body('Sales return updated successfully!.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        // Revert the original sale record's effects
        $this->revertOriginalSale($record);

    }

    protected function revertOriginalSale($sale) 
    {
        // Revert the stock and stock history
        foreach ($sale->returnitems as $item) {
            $product = Product::find($item->product_id);
            $warehouseId = $sale->warehouse_id;

            if ($product->product_type == "Single") {
                $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                $inventory->qty -= $item->qty;
                $inventory->save();

                Stock_History::where('adjustment_item_id', $item->id)->delete();

            } elseif ($product->product_type == "Variation") {
                $inventory = Product_Warehouse_Inventory::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('variant_id', $item->variant_id)
                    ->first();

                $inventory->qty -= $item->qty;
                $inventory->save();

                Stock_History::where('adjustment_item_id', $item->id)->delete();
            }
        }
    }


    protected function afterSave(): void
    {
        $record = $this->getRecord();

        // Apply the changes with the updated sale data
        $this->applySaleChanges($record);

        // Recalculate stock history for affected products
        $this->recalculateStockHistory($record);
    }

    protected function applySaleChanges($records) {

       //update quantity
        foreach (collect($records->returnitems) as $row) {

            $product = Product::where('id', $row->product_id)->first();

            $product_type = $product->product_type;
            $warehouse = $records->warehouse_id;

            if($product_type == "Single"){

                $stock = Product_Warehouse_Inventory::where('product_id',$product->id)
                ->where('warehouse_id',$warehouse)
                ->first();

                $totalqty = $stock->qty + $row->qty;
                $stock->qty = $totalqty;
                $stock->save();

                //update history
                $avaliableqty = $totalqty;

                //update stock from warehouse history
                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse,
                    'adjustment_item_id' => $row->id,
                    'type' => 'Sales return',
                    'qty_change' => "+".$row->qty,
                    'new_quantity' => $avaliableqty,
                    'date' => $records->returndate,
                    'reference' => $records->reference_number
                ];
                
                Stock_History::create($stockout);

            }elseif ($product_type == "Variation"){

                $stock = Product_Warehouse_Inventory::where('product_id',$product->id)
                ->where('warehouse_id',$warehouse)
                ->where('variant_id',$row->variant_id)
                ->first();

                $totalqty = $stock->qty + $row->qty;
                $stock->qty = $totalqty;
                $stock->save();

                //update history
                $avaliableqty = $totalqty;

                //update stock from warehouse history
                $stockout = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse,
                    'variant_id' => $row->variant_id,
                    'adjustment_item_id' => $row->id,
                    'type' => 'Sales return',
                    'qty_change' => "+".$row->qty,
                    'new_quantity' => $avaliableqty,
                    'date' => $records->returndate,
                    'reference' => $records->reference_number
                ];
                
                Stock_History::create($stockout); 

            }

        }
    }

    protected function recalculateStockHistory($sale) {
        foreach ($sale->saleitem as $item) {
            $productId = $item->product_id;
            $warehouseId = $sale->warehouse_id;
            $variantId = $item->variant_id;

            // Retrieve all stock history records for the product in the warehouse, ordered by date
            $stockHistories = Stock_History::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->when($variantId, function ($query, $variantId) {
                    return $query->where('variant_id', $variantId);
                })
                ->orderBy('date','desc')
                ->get();
            
            // Recalculate new quantities
            $inventory = Product_Warehouse_Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->when($variantId, function ($query, $variantId) {
                    return $query->where('variant_id', $variantId);
                })
                ->first();

            $currentQty = $inventory->qty;

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
                    //deleted some stuffs
                }

                $count ++;
            }

        }
    }





}
