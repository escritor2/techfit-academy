<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Histórico de vendas do usuário logado.
     */
    public function index(Request $request)
    {
        $sales = Sale::where('user_id', $request->user()->id)
            ->with('items.product:id,name,image_url')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json($sales);
    }

    /**
     * Cria uma venda com itens e decrementa estoque.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $totalPrice = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Estoque insuficiente para: {$product->name}. Disponível: {$product->stock_quantity}"
                    ]);
                }

                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product->price,
                ];

                // Decrementa estoque
                $product->decrement('stock_quantity', $item['quantity']);
            }

            $sale = Sale::create([
                'user_id'     => $request->user()->id,
                'total_price' => $totalPrice,
                'status'      => 'paid',
            ]);

            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            return response()->json(
                $sale->load('items.product:id,name'),
                201
            );
        });
    }
}
