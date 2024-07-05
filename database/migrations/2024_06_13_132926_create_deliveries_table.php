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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->text('shipping_detail');
            $table->text('shipping_address')->nullable();
            $table->string('shipping_status')->nullable();
            $table->string('delivered_to')->nullable();
            $table->text('shipping_note')->nullable();
            $table->date('expected');
            $table->date('deliveredon')->nullable();
            $table->text('shipping_documents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
