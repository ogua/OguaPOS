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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('reference_number')->nullable();
            $table->string('sales_type')->default("NORMAL");
            $table->integer('user_id');
            $table->integer('cash_register_id');
            $table->integer('customer_id')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->integer('biller_id')->nullable();
            $table->integer('item');
            $table->integer('total_qty');
            $table->decimal('total_price', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2)->default(0);
            $table->integer('currency_id')->nullable();
            $table->integer('exchange_rate')->nullable();
            $table->string('order_tax_rate')->nullable();
            $table->string('order_tax_value')->nullable();
            $table->string('order_tax')->nullable();
            $table->string('order_discount_type')->nullable();
            $table->decimal('order_discount_value',10, 2)->nullable();
            $table->decimal('total_discount',10, 2)->nullable();
            $table->integer('coupon_id')->nullable();
            $table->decimal('coupon_discount',10, 2)->nullable();
            $table->decimal('shipping_cost',10, 2)->nullable();
            $table->integer('sale_status')->nullable();
            $table->integer('payment_status')->nullable();
            $table->text('sale_note')->nullable();
            $table->text('staff_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
