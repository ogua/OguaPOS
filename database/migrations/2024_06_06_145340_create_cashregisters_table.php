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
        Schema::create('cashregisters', function (Blueprint $table) {
            $table->id();
            $table->decimal('cash_in_hand',10,2);
            $table->bigInteger('user_id');
            $table->bigInteger('warehouse_id');
            $table->boolean('status')->default(false);
            $table->date('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashregisters');
    }
};
