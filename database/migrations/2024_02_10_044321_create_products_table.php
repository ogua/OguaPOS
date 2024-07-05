<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
//promitional_price,start,end

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_code');
            $table->string('barcode_symbology')->nullable();
            $table->integer('brand_id')->nullable();
            $table->unsignedBigInteger('product_category_id');
            $table->integer('product_unit_id');
            $table->integer('sale_unit_id')->nullable();
            $table->integer('purchase_unit_id')->nullable();
           // $table->string('product_cost');
           // $table->string('product_price');
            $table->string('daily_sales_objectives')->nullable();
            $table->string('alert_quantity')->nullable();
            $table->integer('product_tax')->nullable();
            $table->integer('tax_method')->nullable();
            $table->string('product_image')->nullable();
            $table->text('product_details')->nullable();
            $table->date('product_expiry_date')->nullable();
            $table->string('product_batch_number')->nullable();
            $table->boolean('promotional_price')
            ->default(false)
            ->nullable();
            //$table->unsignedBigInteger('product_warehouse_id')->nullable();
            //$table->integer('product_qty');
            $table->unsignedBigInteger('user_created_id')->nullable();
            $table->boolean('active')->default(true);
            $table->string('product_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
