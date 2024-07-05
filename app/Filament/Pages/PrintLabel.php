<?php

namespace App\Filament\Pages;

use App\Models\Possettings;
use App\Models\Product;
use App\Models\Taxrates;
use Filament\Pages\Page;

class PrintLabel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static string $view = 'filament.pages.print-label';
    protected static ?string $slug = 'products/label';
    protected static ?string $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Print Labels';
    protected static ?string $modelLabel = 'Print Label';
    protected static ?int $navigationSort = 3;

    public $products = [];
    public $totalproducts = 0;
    public $search = "";
    public $searchresults = [];
    public $selecteditems = [];
    public $ProductName;
    public $variation;
    public $productPrice;
    public $incTax = false;
    public $exTax;
    public $businessname;
    public $expirydate;
    public $packingdate;
    public $barcode_setting;

    public function mount() 
    {
        $this->ProductName = true;
        $this->variation = true;
        $this->productPrice = true;
        $this->exTax = true;
        $this->businessname = true;
        $this->expirydate = true;
        $this->packingdate = true;

        $this->loaddata();
    }

    public function loaddata()
    {
        $this->products = [];

        // Load Single type products
        $singleproduct = Product::where('active', true)
            ->where('product_type', 'Single')
            ->get();

        $pos = Possettings::first();

        foreach ($singleproduct as $product) {

            $product_variant_data = $product->inventory()
               // ->where('warehouse_id',$get('warehouse_id'))
                ->first();

            $main = $product->product_name ?? "";

            $px = $product_variant_data->selling_price;

            $tax = Taxrates::whereIn("id",array_values($product->taxes()->pluck('tax_id')->toArray()))->sum('rate');
                        
            //check task method if its exclusive
            if ($product->tax_method == "1") {
                $newpx = ($px + ($tax / 100) * $px);
            }else{
                $newpx = $px;
            }

            $productData = [
                'id' => $product->id,
                'product_name' => $main,
                'product_code' => $product->product_code,
                'product_image' => $product->product_image,
                'size' => '',
                'product_expiry_date' =>$product->product_expiry_date,
                'cost_price' => $product_variant_data->cost_price,
                'selling_price' => $product_variant_data->selling_price,
                'include_tax' => $px,
                'exclude_tax' => $newpx,
                'company_name' => $pos->company?->name,
                'barcode' => $product->barcode_symbology,
            ];

            $this->products[] = $productData;
        }

        // Load Variation type products
        $products_variation_list = Product::where('active', true)
            ->where('product_type', 'Variation')
            ->get();

        foreach ($products_variation_list as $product) {

            $product_variant_data = $product->variationitems()->orderBy('position')->get();

            $main = $product->product_name ?? "";

            foreach ($product_variant_data as $row) {

                //$product_variant = $product->inventory()
               //  ->where('variant_id',$row->variants_id)
               // ->where('warehouse_id',$get('warehouse_id'))
               // ->first();

               // dd($row);

                $px = $row->selling_price;

                $tax = Taxrates::whereIn("id",array_values($product->taxes()->pluck('tax_id')->toArray()))->sum('rate');
                            
                //check task method if its exclusive
                if ($product->tax_method == "1") {
                    $newpx = ($px + ($tax / 100) * $px);
                }else{
                    $newpx = $px;
                }

                $productData = [
                    'id' => $product->id,
                    'product_name' => $main . "($row->item_name)",
                    'product_code' => $product->product_code ?? "",
                    'product_image' => $product->product_image,
                    'size' => $row->item_name,
                    'product_expiry_date' =>$product->product_expiry_date,
                    'cost_price' => $row->cost_price,
                    'selling_price' => $row->selling_price,
                    'include_tax' => $px,
                    'exclude_tax' => $newpx,
                    'company_name' => $pos->company?->name,
                    'barcode' => $product->barcode_symbology,
                ];

                $this->products[] = $productData;
            }
        }

        $this->totalproducts = count($this->products);
    }

    public function search_product_for_label()
    {
        $searchTerm = strtolower($this->search);

        if ($searchTerm == "") {
            $this->searchresults = [];
            return;
        }

        $this->searchresults = array_filter($this->products, function ($product) use ($searchTerm) {
            return stripos($product['product_name'], $searchTerm) !== false || stripos($product['product_code'], $searchTerm) !== false;
        });

        // Convert search results to a numerically indexed array
        $this->searchresults = array_values($this->searchresults);

        // Check if there's exactly one result
        if (count($this->searchresults) === 1) {

             $item = [
                'id' => $this->searchresults[0]['id'],
                'product_name' => $this->searchresults[0]['product_name'],
                'product_label' => 1,
                'product_code' => $this->searchresults[0]['product_code'],
                'size' => $this->searchresults[0]['size'],
                'product_expiry_date' => null,
                'product_packing_date' => null,
                'product_image' => $this->searchresults[0]['product_image'],
                'cost_price' => $this->searchresults[0]['cost_price'],
                'selling_price' => $this->searchresults[0]['selling_price'],
                'include_tax' => $this->searchresults[0]['include_tax'],
                'exclude_tax' => $this->searchresults[0]['exclude_tax'],
                'company_name' => $this->searchresults[0]['company_name'],
                'barcode' => $this->searchresults[0]['barcode'],
            ];
            $this->add_selected_item($item);

            $this->search = "";
        }
    }

    public function add_selected_item($item)
    {
        // Avoid adding duplicates
        if (!in_array($item, $this->selecteditems)) {
            $this->selecteditems[] = $item;
        }
    }


    public function additems($id, $productName,$productCode,$cost_price,$selling_price,$size,$include_tax,$exclude_tax,$company_name,$barcode)
    {
        // Example logic for adding items
        $item = [
            'id' => $id,
            'product_name' => $productName,
            'product_label' => 1,
            'product_code' => $productCode,
            'size' => $size,
            'product_expiry_date' => null,
            'product_packing_date' => null,
            'product_image' => null,
            'cost_price' => $cost_price,
            'selling_price' => $selling_price,
            'include_tax' => $include_tax,
            'exclude_tax' => $exclude_tax,
            'company_name' => $company_name,
            'barcode' => $barcode,
        ];

        logger($item);

        // Avoid adding duplicates
        if (!in_array($item, $this->selecteditems)) {
            $this->selecteditems[] = $item;
        }

        $this->search = "";

        $this->searchresults = [];
    }

    public function removeItem($index)
    {
        unset($this->selecteditems[$index]);
        $this->selecteditems = array_values($this->selecteditems); // Re-index the array
    }


    public function Productnameupdate() {
        $this->ProductName !=$this->ProductName;
    }

    public function variationupdate() {
        $this->variation !=$this->variation;
    }

    public function productPriceupdate() {
        $this->productPrice !=$this->productPrice;
    }

    public function exTaxupdate() {
        $this->exTax !=$this->exTax;
    }

    public function incTaxupdate() {
        $this->incTax !=$this->incTax;
    }

    public function businessnameupdate() {
        $this->businessname !=$this->businessname;
    }

    public function expirydateupdate() {
        $this->expirydate !=$this->expirydate;
    }

    public function packingdateupdate() {
        $this->packingdate !=$this->packingdate;
    }
}