<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use App\Models\Product;
use App\Models\Product_Warehouse;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Stock_History;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
      //  $data['reference_no'] = auth()->user()->id;

        if ($data['reference_no'] == "") {
            
            $maxcode = DB::table('stock_transfers')
            ->where('reference_no','like','%ST%')
            ->latest()
            ->first();
            
            if ($maxcode) {
                $max = substr($maxcode->reference_no, 7);
                $number = $max + 1;

                if (strlen($number) === 1) {
                  $code = "ST".date('Y')."/000".$number;
                }elseif (strlen($number) === 2) {
                   $code = "ST".date('Y')."/00".$number;
                }elseif (strlen($number) === 3) {
                   $code = "ST".date('Y')."/0".$number;
                }else{
                    $code = "ST".date('Y')."/".$number;
                }
                
            }else{
                $code = "ST".date('Y')."/0001";
            }

            $data['reference_no'] = $code;
        }
        
        return $data;
    }

     protected function afterCreate(): void
    {
        $records = $this->getRecord();

        $fromwarehouse = $records->from_warehouse_id;
        $towarehouse = $records->to_warehouse_id;
        
        foreach ($records->transferitems as $stock) {
            
            $producttype = $stock->product->product_type;
            $productid = $stock->product_id;
            $variantid = $stock->variant_id;
            $qty = $stock->qty;
            
            if ($producttype == "Single") {
                
                $fromstock = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('warehouse_id',$fromwarehouse)
                ->first();
                $totalqty = $fromstock->qty - $qty;
                $fromstock->qty = $totalqty;
                $fromstock->save();


                $tostock = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('warehouse_id',$towarehouse)
                ->first();

                if ($tostock) {
                    $totalqty = $tostock->qty + $qty;
                    $tostock->qty = $totalqty;
                    $tostock->save();
                }else {
                    $newstock = [
                        'product_id' => $productid,
                        'warehouse_id' => $towarehouse,
                        'qty' => $qty,
                        'cost_price' => $fromstock->cost_price,
                        'selling_price' => $fromstock->selling_price,
                    ];

                    $tostock = new Product_Warehouse_Inventory($newstock);
                    $tostock->save();
                    
                    $product = Product::where('id',$productid)->first();

                    if (!$product->warehouses()->where('warehouse_id',$towarehouse)->exists()) {
                       $product->warehouses()->attach($towarehouse);
                    }
                }

                
            }elseif ($producttype == "Variation") {
                $fromstock = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('variant_id',$variantid)
                ->where('warehouse_id',$fromwarehouse)
                ->first();

                $totalqty = $fromstock->qty - $qty;
                $fromstock->qty = $totalqty;
                $fromstock->save();

                $tostock = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('variant_id',$variantid)
                ->where('warehouse_id',$towarehouse)
                ->first();

                if ($tostock) {
                    $totalqty = $tostock->qty + $qty;
                    $tostock->qty = $totalqty;
                    $tostock->save();
                }else {
                    $newstock = [
                        'product_id' => $productid,
                        'variant_id' => $variantid,
                        'warehouse_id' => $towarehouse,
                        'qty' => $qty,
                        'cost_price' => $fromstock->cost_price,
                        'selling_price' => $fromstock->selling_price,
                    ];

                    $tostock = new Product_Warehouse_Inventory($newstock);
                    $tostock->save();

                    $product = Product::where('id',$productid)->first();

                    if (!$product->warehouses()->where('warehouse_id',$towarehouse)->exists()) {
                       $product->warehouses()->attach($towarehouse);
                    }

                }
            }
            
            //update stock from warehouse history
            $stockout = [
                'product_id' => $productid,
                'warehouse_id' => $fromwarehouse,
                'variant_id' => $variantid,
                'adjustment_item_id' => $stock->id,
                'type' => 'Stock Transfer (Out)',
                'qty_change' => "-".$qty,
                'new_quantity' => $fromstock->qty,
                'date' => now(),
                'reference' => $records->reference_no,
            ];
            
            Stock_History::create($stockout);


             //update stock from warehouse history
            $stockin = [
                'product_id' => $productid,
                'warehouse_id' => $towarehouse,
                'variant_id' => $variantid,
                'adjustment_item_id' => $stock->id,
                'type' => 'Stock Transfer (In)',
                'qty_change' => "+".$qty,
                'new_quantity' => $tostock->qty,
                'date' => now(),
                'reference' => $records->reference_no
            ];
            
            Stock_History::create($stockin);
        }
        
    }




}
