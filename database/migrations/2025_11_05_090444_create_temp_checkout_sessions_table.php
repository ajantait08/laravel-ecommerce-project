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
        Schema::create('temp_checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('product_id');
            $table->string('user_id');
            $table->string('user_email');
            $table->integer('quantity');
            $table->string('name');
            $table->string('description');
            $table->string('image');
            $table->string('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_checkout_sessions');
    }
};
