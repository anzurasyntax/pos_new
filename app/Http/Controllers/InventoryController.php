<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');

        $products = Product::query()
            ->when(filled($q), function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%'.$q.'%')
                        ->orWhere('sku', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory', [
            'products' => $products,
            'q' => $q,
            // Beginner-friendly threshold; you can change this later.
            'lowStockThreshold' => 5,
        ]);
    }
}
