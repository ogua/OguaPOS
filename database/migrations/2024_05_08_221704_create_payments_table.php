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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('sale_return_id')->nullable();
            $table->unsignedBigInteger('purchase_return_id')->nullable();
            $table->unsignedBigInteger('cash_register_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('fund_transfer_id')->nullable();
            $table->enum('payment_type',['debit','credit'])->nullable("debit");
            $table->string('payment_ref')->nullable();
            $table->string('paying_type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('balance',10,2)->default(0);
            $table->decimal('amount',10,2)->nullable();
            $table->integer('used_points')->nullable();
            $table->decimal('change',10,2)->nullable();
            $table->string('cheque_no')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_stripe_id')->nullable();
            $table->string('charge_id')->nullable();
            $table->unsignedBigInteger('gift_card_id')->nullable();
            $table->string('paypal_transaction_id')->nullable();
            $table->string('paying_method')->nullable();
            $table->text('payment_note')->nullable();
            $table->string('bankname')->nullable();
            $table->decimal('paid_on',10,2)->nullable();
            $table->string('accountnumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
