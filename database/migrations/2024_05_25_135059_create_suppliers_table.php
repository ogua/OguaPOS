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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('contact_type');
            $table->string('contact');
            $table->string('title')->nullable();
            $table->string('firstname')->nullable();
            $table->string('surname')->nullable();
            $table->string('other_names')->nullable();
            $table->string('additional_contact')->nullable();
            $table->string('landline')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
