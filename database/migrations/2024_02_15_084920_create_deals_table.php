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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('value');
            $table->string('currency');
            $table->date('closingdate')->nullable();
            $table->string('label');
            $table->boolean('wonorlost')->default(false);
            $table->boolean('status')->default(false);
            $table->unsignedBigInteger('user_created_id')->nullable();
            $table->foreign('user_created_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
