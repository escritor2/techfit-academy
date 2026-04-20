<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Lista todos os produtos (público).
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Cria novo produto (admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image_url'      => 'nullable|string|max:500',
        ]);

        $product = Product::create($request->all());

        return response()->json($product, 201);
    }

    /**
     * Exibe um produto específico.
     */
    public function show(string $id)
    {
        return response()->json(Product::findOrFail($id));
    }

    /**
     * Atualiza produto (admin).
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'category'       => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'image_url'      => 'nullable|string|max:500',
        ]);

        $product->update($request->all());

        return response()->json($product);
    }

    /**
     * Remove produto (admin).
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produto removido com sucesso.']);
    }
}
