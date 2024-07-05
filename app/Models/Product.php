<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class,"warehouse_id");
    }

    public function category()
    {
        return $this->belongsTo(Productcategory::class, 'product_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class,'brand_id');
    }

    public function promotion() {
        return $this->hasOne(Productpromotionpx::class,"product_id");
    }

    public function tax() {
        return $this->belongsTo(Taxrates::class,"product_tax");
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class,"product_warehouse_id");
    }

    // public function warehouses()
    // {
    //     return $this->belongsToMany(Warehouse::class,'product__warehouses','product_id','warehouse_id')
    //     ->withPivot('id','qty', 'cost_price', 'selling_price', 'wholesale_price','created_at','updated_at');
    // }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class,'product__warehouses','product_id','warehouse_id');
    }


    public function warehousepx()
    {
        return $this->belongsToMany(Warehouse::class,'product__warehouses','product_id','warehouse_id');
    }

    public function taxes()
    {
        return $this->belongsToMany(Taxrates::class,'product__taxes','product_id','tax_id');
    }

    public function inventory()
    {
        return $this->hasMany(Product_Warehouse_Inventory::class,'product_id');
    }

    public function variationitems() : HasMany {
        return $this->hasMany(Product_variation::class,"product_id");
    }

    public function getFirstAndLastVariantPrice() {
        $variant = $this->variationitems()->get();
        return ($variant->first()->selling_price ?? 0)." - ".($variant->last()->selling_price ?? 0);
    }

    public function getFirstVariantPrice() {
        $variant = $this->variationitems()->get();
        return $variant->first()->selling_price ?? 0;
    }

    public function getLastVariantPrice() {
        $variant = $this->variationitems()->get();
        return $variant->last()->selling_price ?? 0;
    }

    public function getinventorypx() {

        $inventory = $this->inventory()->get();

        return [
            'costpx' => $inventory->first()->cost_price ?? 0,
            'sellpx' => $inventory->first()->selling_price ?? 0
        ];
    }

    public function getinventoryqty() {

        $inventory = $this->inventory()->get();

        return [
            'qty' => $inventory->first()->qty ?? 0,
        ];
    }

    public function unit()
    {
        return $this->belongsTo(Productunit::class,'product_unit_id');
    }

    public function history()
    {
        return $this->hasMany(Stock_History::class,'product_id')
        ->orderBy('date','desc');
    }

    public function promotions()
    {
        return $this->hasMany(ProductPromotion::class,'product_id');
    }





}
