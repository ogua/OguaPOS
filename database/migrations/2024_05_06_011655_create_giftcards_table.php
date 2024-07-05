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
        Schema::create('giftcards', function (Blueprint $table) {
            $table->id();
            $table->string('card_no');
            $table->decimal('amount', 10, 2);
            $table->integer('expense')->default(0);
            $table->date('expiry_date');
            $table->integer('user_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giftcards');
    }
};
