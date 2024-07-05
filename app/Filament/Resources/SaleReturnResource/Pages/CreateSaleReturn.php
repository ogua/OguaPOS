<?php

namespace App\Filament\Resources\SaleReturnResource\Pages;

use App\Filament\Resources\SaleReturnResource;
use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSaleReturn extends CreateRecord
{
    protected static string $resource = SaleReturnResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
         $record = $this->getRecord();

        return Notification::make()
            ->success()
            ->title('Return created')
            ->body('Sales return recorded successfully!.');
    }

    protected function afterCreate(): void
    {
        $records = $this->getRecord();
        
        //update quantity
        foreach (collect($records->returnitems) as $row) {

            $product = Product::where('id', $row->product_id)->first();

            $product_type = $product->product_type;
            $warehouse = $records->sale?->warehouse_id;

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
                    'reference' => $records->reference_no
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
                    'reference' => $records->reference_no
                ];
                
                Stock_History::create($stockout); 

            }

        }

    }

}
