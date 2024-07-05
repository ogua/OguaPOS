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
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transfer_from');
            $table->unsignedBigInteger('transfer_to');
            $table->decimal('amount',10,2);
            $table->decimal('from_balance',10,2)->nullable();
            $table->decimal('to_balance',10,2)->nullable();
            $table->dateTime('transfer_date');
            $table->text('note')->nullable();
            $table->string('transfer_type')->default("TRANSFER");
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
