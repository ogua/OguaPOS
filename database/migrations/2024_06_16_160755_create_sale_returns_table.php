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
        
//         ``, `total_qty`, `total_discount`, `total_tax`, `total_price`,
//  `item`, `order_tax`, `grand_total`, `order_tax_rate`, `return_note`, `staff_note`, 
//  `reference_no`, `user_id`, `customer_id`, `warehouse_id`, `biller_id`, `cash_register_id`, 
//  `account_id`, `updated_at`, `created_at` 

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cash_register_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->integer('total_qty')->nullable();
            $table->integer('total_discount')->nullable();
            $table->integer('total_tax')->nullable();
            $table->integer('item')->nullable();
            $table->decimal('order_tax',10,2);
            $table->decimal('grand_total',10,2);
            $table->text('return_note')->nullable();
            $table->text('staff_note')->nullable();
            $table->dateTime('returndate');
            $table->string('payment_status')->nullable();
            $table->decimal('total_amount',10,2);
            $table->decimal('amount_paid',10,2);
            $table->decimal('amount_due',10);
            $table->string('document')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_returns');
    }
};
