<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku', 64)->unique();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->index(['category_id', 'price', 'id'], 'products_category_price_id_idx');
            $table->index(['price', 'id'], 'products_price_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
