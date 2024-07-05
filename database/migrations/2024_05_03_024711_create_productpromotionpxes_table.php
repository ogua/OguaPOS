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
        Schema::create('productpromotionpxes', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->decimal('promotion_price', 10, 2);
            $table->date('promotion_start');
            $table->date('promotion_end');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productpromotionpxes');
    }
};
