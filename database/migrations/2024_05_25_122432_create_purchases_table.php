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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suppier_id');
            $table->unsignedBigInteger('user_id');
            $table->string('reference_no')->nullable();
            $table->dateTime('purchase_date');
            $table->string('purchase_status');
            $table->string('payment_status');
            $table->unsignedBigInteger('warehouse_id');
            $table->string('per_term')->nullable();
            $table->string('per_month')->nullable();
            $table->string('attach_document')->nullable();
            $table->string('discount_type')->nullable("Flat");
            $table->decimal('discount_amount',10,2)->default(0);
            $table->decimal('purchasetax',10,2)->default(0);
            $table->text('additional_note')->nullable();
            $table->string('shipping_details')->nullable();
            $table->decimal('shipping_cost',10,2)->default(0);
            $table->decimal('total_cost', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2)->default(0);
            $table->integer('item')->default(0);
            $table->integer('total_qty')->default(0);
            $table->integer('total_dscnt')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
