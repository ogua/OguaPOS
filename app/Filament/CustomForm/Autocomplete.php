<?php

namespace App\Filament\CustomForm;

use App\Models\Product;
use App\Support\ProductSearchImage;
use Filament\Forms\Components\Select;

class Autocomplete extends Select
{
    public Product $product;

   // protected string $view = 'forms.components.autocomplete';


    protected function setUp(): void
    {
        parent::setUp();

        $this->options($this->loadproducts());
    }

     public function loadproducts(): array
    {
        $products = [];
        // Load Single type products
        $products = Product::select('id', 'product_name', 'product_code', 'product_image')
            ->where('active', true)
            ->where('product_type', 'Single')
            ->get()
            ->toArray(); // Convert to array

        // Load Variation type products
        $products_variation_list = Product::select('id', 'product_name', 'product_code', 'product_image','product_expiry_date')   
            ->where('active', true)
            ->where('product_type', 'Variation')
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

    



}