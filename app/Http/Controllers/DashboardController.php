<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStockThreshold = 5;

        $totalSales = (float) Sale::query()->sum('total_amount');
        $totalPurchases = (float) Purchase::query()->sum('total_amount');
        $profit = $totalSales - $totalPurchases;

        $lowStockItems = Product::query()
            ->where('stock_quantity', '<=', $lowStockThreshold)
            ->orderBy('stock_quantity')
            ->limit(6)
            ->get(['name', 'sku', 'stock_quantity']);

        $lowStockCount = $lowStockItems->count();

        $recentSales = Sale::query()
            ->with('customer')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'profit' => $profit,
            'lowStockItems' => $lowStockItems,
            'lowStockCount' => $lowStockCount,
            'recentSales' => $recentSales,
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }
}
