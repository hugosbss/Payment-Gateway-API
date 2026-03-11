<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::query()->get();

        return response()->json([
            'message' => 'Produtos encontrados',
            'data' => $products,
        ]);
    }

    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Produto encontrado',
            'data' => $product,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Produto criado com sucesso',
            'data' => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Produto atualizado com sucesso',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Produto deletado com sucesso',
        ]);
    }
}
