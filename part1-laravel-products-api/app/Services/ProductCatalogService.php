<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator;

class ProductCatalogService
{
    public function paginate(array $filters): Paginator
    {
        $perPage = (int) ($filters['per_page'] ?? 20);

        $query = Product::query()
            ->select(['id', 'category_id', 'sku', 'name', 'price'])
            ->with([
                'category:id,name,slug',
                'stock:product_id,quantity',
            ])
            ->when(isset($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', (int) $filters['category_id']);
            })
            ->when(isset($filters['price_from']), function ($query) use ($filters) {
                $query->where('price', '>=', $filters['price_from']);
            })
            ->when(isset($filters['price_to']), function ($query) use ($filters) {
                $query->where('price', '<=', $filters['price_to']);
            })
            ->when(array_key_exists('in_stock', $filters), function ($query) use ($filters) {
                $query->whereHas('stock', function ($stockQuery) use ($filters) {
                    if ((bool) $filters['in_stock']) {
                        $stockQuery->where('quantity', '>', 0);

                        return;
                    }

                    $stockQuery->where('quantity', '=', 0);
                });
            })
            ->orderBy('id');

        return $query->simplePaginate($perPage)->withQueryString();
    }
}
