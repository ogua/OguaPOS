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
        Schema::create('dealsproducts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deals_id');
            $table->foreign('deals_id')->references('id')->on('deals');
            $table->unsignedBigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on("products");
            $table->integer('price');
            $table->integer('qty');
            $table->string('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealsproducts');
    }
};
