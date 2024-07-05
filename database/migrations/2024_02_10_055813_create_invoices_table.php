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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('user_id');
            $table->string("pricetype")->default("DEFAULT");
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('sent')->default(false);
            $table->integer('item');
            $table->integer('total_qty');
            $table->decimal('total_price', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->string('order_tax_rate')->nullable();
            $table->string('order_tax_value')->nullable();
            $table->string('order_tax')->nullable();
            $table->string('order_discount_type')->nullable();
            $table->decimal('order_discount_value',10, 2)->nullable();
            $table->decimal('total_discount',10, 2)->nullable();
            $table->decimal('shipping_cost',10, 2)->nullable();
            $table->integer('quotation_status')->nullable();
            $table->text('terms')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
