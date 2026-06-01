<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->relationLoaded('category') ? $this->category : null;
        $stock = $this->relationLoaded('stock') ? $this->stock : null;
        $quantity = $stock ? $stock->quantity : 0;

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => (float) $this->price,
            'category' => [
                'id' => $category ? $category->id : null,
                'name' => $category ? $category->name : null,
                'slug' => $category ? $category->slug : null,
            ],
            'stock' => [
                'quantity' => $quantity,
                'in_stock' => $quantity > 0,
            ],
        ];
    }
}
