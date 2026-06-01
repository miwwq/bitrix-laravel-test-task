<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductCatalogService;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request, ProductCatalogService $catalog)
    {
        $products = $catalog->paginate($request->validated());

        return ProductResource::collection($products);
    }
}
