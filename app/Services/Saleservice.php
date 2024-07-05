<?php
namespace App\Services;

use App\Models\Product;
use App\Models\Product_Warehouse_Inventory;
use App\Models\Purchase;
use App\Models\Stock_History;
use App\Support\ProductSearchImage;

class Saleservice{


    public function getproductdetails(string $search): array
    {
        $results = Product::where('product_name', 'like', "%{$search}%")
        ->orWhere('product_code', 'like', "%{$search}%")
        ->get();
 
        return collect($results)
            ->mapWithKeys(function ($result): array {
                return [$result['id'] => $result['product_code'] . '( ' . $result['product_name'].' )'];
            })
            ->toArray();
    }


    public function getadjustmentproduct(string $search, string $warehouse_id): array
    {
        $products = [];
        // Load Single type products
        $products = Product::select('id', 'product_name', 'product_code', 'product_image')
            ->whereHas('warehouses',function($query) use($warehouse_id){
                $query->where('warehouse_id', $warehouse_id);
            })
            ->where('active', true)
            ->where('product_type', 'Single')
            ->where('product_name', 'like', "%{$search}%")
            ->orWhere('product_code', 'like', "%{$search}%")
            ->get()
            ->toArray(); // Convert to array

        // Load Variation type products
        $products_variation_list = Product::select('id', 'product_name', 'product_code', 'product_image','product_expiry_date')
            ->whereHas('warehouses',function($query) use($warehouse_id){
                $query->where('warehouse_id', $warehouse_id);
            })    
            ->where('active', true)
            ->where('product_type', 'Variation')
             ->where('product_name', 'like', "%{$search}%")
            ->orWhere('product_code', 'like', "%{$search}%")
            ->get();

        foreach ($products_variation_list as $product) {
            $product_variant_data = $product->variationitems()->orderBy('position')->get();

            $main = $product->product_name ?? "";

            foreach ($product_variant_data as $row) {
                $productData = [
                    'id' => $product->id.','.$row->id,
                    'product_name' => $main . "($row->item_name)",
                    'product_code' => $row->item_name ?? "",
                    'product_image' => $product->product_image,
                    'product_expiry_date' =>$product->product_expiry_date
                ];
                $products[] = $productData;
            }
        }
 
        return collect($products)
            ->map(function ($result): array {
                return [$result['id'] => ProductSearchImage::getOptionString($result)];
            })
            ->toArray();
    }


    public function getadallproduct(string $search, string $warehouse_id): array
    {
        $products = [];
        // Load Single type products
        $products = Product::select('id', 'product_name', 'product_code', 'product_image')
            ->whereHas('warehouses',function($query) use($warehouse_id){
                $query->where('warehouse_id','!=',$warehouse_id);
            })
            ->where('active', true)
            ->where('product_type', 'Single')
            ->where('product_name', 'like', "%{$search}%")
            ->orWhere('product_code', 'like', "%{$search}%")
            ->get()
            ->toArray(); // Convert to array

        // Load Variation type products
        $products_variation_list = Product::select('id', 'product_name', 'product_code', 'product_image','product_expiry_date')
            ->whereHas('warehouses',function($query) use($warehouse_id){
                $query->where('warehouse_id','!=',$warehouse_id);
            })    
            ->where('active', true)
            ->where('product_type', 'Variation')
             ->where('product_name', 'like', "%{$search}%")
            ->orWhere('product_code', 'like', "%{$search}%")
            ->get();

        foreach ($products_variation_list as $product) {
            $product_variant_data = $product->variationitems()->orderBy('position')->get();

            $main = $product->product_name ?? "";

            foreach ($product_variant_data as $row) {
                $productData = [
                    'id' => $product->id.','.$row->id,
                    'product_name' => $main . "($row->item_name)",
                    'product_code' => $row->item_name ?? "",
                    'product_image' => $product->product_image,
                    'product_expiry_date' =>$product->product_expiry_date
                ];
                $products[] = $productData;
            }
        }
 
        return collect($products)
            ->map(function ($result): array {
                return [$result['id'] => $result['product_name']];
            })
            ->toArray();
    }


    public function createdstockitems($record) {
        $purchase = Purchase::with('purchaseitmes')->where('id',$record)->first();
        $warehouse = $purchase->warehouse_id;

        foreach ($purchase->purchaseitmes as $stock) {
            $productid = $stock->product_id;
            $producttype = $stock->product?->product_type;
            $variantid = $stock->variant_id;
            $qty = $stock->qty;
            
            if ($producttype == "Single") {

                $updateinventory = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->first();

                if ($updateinventory) {
                    $newqty = $updateinventory->qty + $qty;
                    $updateinventory->qty=$newqty;
                    $updateinventory->save();
                }else {

                    $newstock = [
                        'product_id' => $productid,
                        'warehouse_id' => $warehouse,
                        'qty' => $qty,
                        'cost_price' => $stock->cost_price,
                        'selling_price' => $stock->selling_price,
                    ];

                    $updateinventory = new Product_Warehouse_Inventory($newstock);
                    $updateinventory->save();
                    
                    $product = Product::where('id',$productid)->first();

                    if (!$product->warehouses()->where('warehouse_id',$warehouse)->exists()) {
                       $product->warehouses()->attach($warehouse);
                    }
                }

                $history = Stock_History::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->where('adjustment_item_id',$stock->id)
                ->first();
                
            }elseif ($producttype == "Variation") {

                $updateinventory = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('variant_id',$variantid)
                ->where('warehouse_id',$warehouse)
                ->first();

                if ($updateinventory) {
                    $newqty = $updateinventory->qty + $qty;
                    $updateinventory->qty=$newqty;
                    $updateinventory->save();
                }else {
                    $newstock = [
                        'product_id' => $productid,
                        'variant_id' => $variantid,
                        'warehouse_id' => $warehouse,
                        'qty' => $qty,
                        'cost_price' => $stock->cost_price,
                        'selling_price' => $stock->selling_price,
                    ];

                    $updateinventory = new Product_Warehouse_Inventory($newstock);
                    $updateinventory->save();

                    $product = Product::where('id',$productid)->first();

                    if (!$product->warehouses()->where('warehouse_id',$updateinventory)->exists()) {
                       $product->warehouses()->attach($updateinventory);
                    }

                }

                $history = Stock_History::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->where('variant_id',$variantid)
                ->where('adjustment_item_id',$stock->id)
                ->first();
            }


            if ($history) {
                $history->qty_change = "+".$qty;
                $history->save();
                self::generateStockReport($producttype,$productid,$variantid,$variantid,$updateinventory->qty,$history->id);
            }else {
                //update stock from warehouse history
                $stockout = [
                    'product_id' => $productid,
                    'warehouse_id' => $warehouse,
                    'variant_id' => $variantid,
                    'adjustment_item_id' => $stock->id,
                    'type' => 'Purchase',
                    'qty_change' => "+".$qty,
                    'new_quantity' => $updateinventory->qty,
                    'date' => $purchase->purchase_date,
                    'reference' => $purchase->reference_no,
                ];
                
                Stock_History::create($stockout);
            }
        }
    }

    public function deletepreviousitems($record) {

        $purchase = Purchase::with('purchaseitmes')->where('id',$record)->first();
        $warehouse = $purchase->warehouse_id;

        foreach ($purchase->purchaseitmes as $stock) {
            $productid = $stock->product_id;
            $producttype = $stock->product?->product_type;
            $variantid = $stock->variant_id;
            $qty = $stock->qty;
            
            if ($producttype == "Single") {

                $history = Stock_History::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->where('adjustment_item_id',$stock->id)
                ->first();

                if ($history) {

                    $updateinventory = Product_Warehouse_Inventory::where('product_id',$productid)
                    ->where('warehouse_id',$warehouse)
                    ->first();
                    

                    if ($updateinventory) {

                        $newqty = $updateinventory->qty - substr($history->qty_change,1);
                        $updateinventory->qty = $newqty;
                        $updateinventory->save();

                        self::generateStockReport($producttype,$productid,$warehouse,$variantid,$newqty,$history->id);
                    }

                    $history->delete();
                }

                
            }elseif ($producttype == "Variation") {

                $history = Stock_History::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->where('variant_id',$variantid)
                ->where('adjustment_item_id',$stock->id)
                ->first();

                if ($history) {

                    $updateinventory = Product_Warehouse_Inventory::where('product_id',$productid)
                    ->where('variant_id',$variantid)
                    ->where('warehouse_id',$warehouse)
                    ->first();

                    if ($updateinventory) {

                        $newqty = $updateinventory->qty - substr($history->qty_change,1);
                        $updateinventory->qty=$newqty;
                        $updateinventory->save();

                        self::generateStockReport($producttype,$productid,$warehouse,$variantid,$updateinventory->qty,$history->id);
                    }

                    $history->delete();
                }

            }

        }
    }

     public function updatedstockitems($record) {
        
        $purchase = Purchase::with('purchaseitmes')->where('id',$record)->first();
        $warehouse = $purchase->warehouse_id;

        foreach ($purchase->purchaseitmes as $stock) {
            $productid = $stock->product_id;
            $producttype = $stock->product?->product_type;
            $variantid = $stock->variant_id;
            $qty = $stock->qty;
            
            if ($producttype == "Single") {

                $updateinventory = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->first();

                if ($updateinventory) {
                    $newqty = $updateinventory->qty + $qty;
                    $updateinventory->qty=$newqty;
                    $updateinventory->save();
                }else {

                    $newstock = [
                        'product_id' => $productid,
                        'warehouse_id' => $warehouse,
                        'qty' => $qty,
                        'cost_price' => $stock->cost_price,
                        'selling_price' => $stock->selling_price,
                    ];

                    $updateinventory = new Product_Warehouse_Inventory($newstock);
                    $updateinventory->save();
                    
                    $product = Product::where('id',$productid)->first();

                    if (!$product->warehouses()->where('warehouse_id',$warehouse)->exists()) {
                       $product->warehouses()->attach($warehouse);
                    }
                }

                $history = Stock_History::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->where('adjustment_item_id',$stock->id)
                ->first();
                
            }elseif ($producttype == "Variation") {

                $updateinventory = Product_Warehouse_Inventory::where('product_id',$productid)
                ->where('variant_id',$variantid)
                ->where('warehouse_id',$warehouse)
                ->first();

                if ($updateinventory) {
                    $newqty = $updateinventory->qty + $qty;
                    $updateinventory->qty=$newqty;
                    $updateinventory->save();
                }else {
                    $newstock = [
                        'product_id' => $productid,
                        'variant_id' => $variantid,
                        'warehouse_id' => $warehouse,
                        'qty' => $qty,
                        'cost_price' => $stock->cost_price,
                        'selling_price' => $stock->selling_price,
                    ];

                    $updateinventory = new Product_Warehouse_Inventory($newstock);
                    $updateinventory->save();

                    $product = Product::where('id',$productid)->first();

                    if (!$product->warehouses()->where('warehouse_id',$warehouse)->exists()) {
                       $product->warehouses()->attach($warehouse);
                    }

                }

                $history = Stock_History::where('product_id',$productid)
                ->where('warehouse_id',$warehouse)
                ->where('variant_id',$variantid)
                ->where('adjustment_item_id',$stock->id)
                ->first();
            }


            if ($history) {
                $history->qty_change = "+".$qty;
                $history->save();
                self::generateStockReport($producttype,$productid,$warehouse,$variantid,$updateinventory->qty,$history->id);
            }else {
                //update stock from warehouse history
                $stockout = [
                    'product_id' => $productid,
                    'warehouse_id' => $warehouse,
                    'variant_id' => $variantid,
                    'adjustment_item_id' => $stock->id,
                    'type' => 'Purchase',
                    'qty_change' => "+".$qty,
                    'new_quantity' => $updateinventory->qty,
                    'date' => now(),
                    'reference' => $purchase->reference_no,
                ];
                
                Stock_History::create($stockout);
            }
        }
    }


    private function generateStockReport($product_type,$product_id,$warehouse_id,$variant_id,$newqty,$id)
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
        
        $currentStock = 0;

        $count = 0;
        
        foreach ($stockHistories as $history) {

            if ($count === 0) {
                $history->new_quantity = $newqty;
                $history->save();
                $currentStock+=$newqty;
            }

            if ($count > 0) {
                $history->new_quantity = $currentStock;
                $history->save();
            }


            if ($history->qty_change < 0) {
                $currentStock += abs($history->qty_change);
            } else {
                $currentStock -= substr($history->qty_change,1);
            }


            $count ++;
        }
        
    }



    private function generateStockReports($product_type,$product_id,$warehouse_id,$variant_id,$newqty,$id)
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
        
        foreach ($stockHistories as $history) {

            if ($history->qty_change < 0) {
                $currentStock -= abs($history->qty_change);
            } else {
                $currentStock += substr($history->qty_change,1);
            }

            $history->new_quantity = $currentStock;
            $history->save();
        }
        
    }



}