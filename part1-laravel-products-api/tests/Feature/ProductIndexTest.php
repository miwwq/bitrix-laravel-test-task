<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_products_by_category_price_and_stock(): void
    {
        $category = Category::create([
            'name' => 'Phones',
            'slug' => 'phones',
        ]);
        $otherCategory = Category::create([
            'name' => 'Laptops',
            'slug' => 'laptops',
        ]);

        $matching = Product::create([
            'category_id' => $category->id,
            'sku' => 'PHONE-1',
            'name' => 'Phone 1',
            'price' => 150,
        ]);
        ProductStock::create([
            'product_id' => $matching->id,
            'quantity' => 5,
        ]);

        $outOfStock = Product::create([
            'category_id' => $category->id,
            'sku' => 'PHONE-2',
            'name' => 'Phone 2',
            'price' => 150,
        ]);
        ProductStock::create([
            'product_id' => $outOfStock->id,
            'quantity' => 0,
        ]);

        $other = Product::create([
            'category_id' => $otherCategory->id,
            'sku' => 'LAPTOP-1',
            'name' => 'Laptop 1',
            'price' => 150,
        ]);
        ProductStock::create([
            'product_id' => $other->id,
            'quantity' => 10,
        ]);

        $response = $this->getJson('/api/products?' . http_build_query([
            'category_id' => $category->id,
            'price_from' => 100,
            'price_to' => 200,
            'in_stock' => 1,
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'PHONE-1')
            ->assertJsonPath('data.0.category.slug', 'phones')
            ->assertJsonPath('data.0.stock.in_stock', true);
    }

    public function test_it_returns_json_validation_errors(): void
    {
        $response = $this->getJson('/api/products?price_from=200&price_to=100&per_page=500');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price_to', 'per_page']);
    }
}
