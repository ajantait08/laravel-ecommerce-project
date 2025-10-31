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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('_id');
            $table->string('userId');
            $table->string('name');
            $table->string('description');
            $table->string('price');
            $table->string('offerPrice');
            $table->json('images');
            $table->string('category');
            $table->bigInteger('timestamp');
            $table->integer('__v');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
