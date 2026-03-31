<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerProductPrice;
use App\Models\CustomerVariantPrice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = strtolower((string) (Auth::user()?->role ?? 'sales_user'));
        $isSalesUser = $role === 'sales_user';
        $canSell = in_array($role, ['manager', 'sales_user', 'super_admin'], true);

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

        $estimateWizard = null;
        if ($canSell) {
            $customers = Customer::query()
                ->orderBy('name')
                ->get(['id', 'name']);

            $products = Product::query()
                ->with('variants')
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'sale_price', 'stock_quantity']);

            $customerProductPrices = CustomerProductPrice::query()
                ->get(['customer_id', 'product_id', 'last_price']);

            $customerVariantPrices = CustomerVariantPrice::query()
                ->get(['customer_id', 'variant_id', 'last_price']);

            $pricesByCustomerProducts = [];
            foreach ($customerProductPrices as $row) {
                $pricesByCustomerProducts[(string) $row->customer_id][(string) $row->product_id] = $row->last_price;
            }

            $pricesByCustomerVariants = [];
            foreach ($customerVariantPrices as $row) {
                $pricesByCustomerVariants[(string) $row->customer_id][(string) $row->variant_id] = $row->last_price;
            }

            $estimateWizard = [
                'customers' => $customers,
                'productsPayload' => $products->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'sale_price' => (float) $p->sale_price,
                    'stock_quantity' => (int) $p->stock_quantity,
                    'variants' => $p->variants->map(fn ($v) => [
                        'id' => $v->id,
                        'variant_name' => $v->variant_name,
                        'sku' => $v->sku,
                        'sale_price' => (float) $v->sale_price,
                        'stock_quantity' => (int) $v->stock_quantity,
                    ])->values(),
                ])->values(),
                'pricesByCustomerProducts' => $pricesByCustomerProducts,
                'pricesByCustomerVariants' => $pricesByCustomerVariants,
            ];
        }

        return view('dashboard', [
            'isSalesUser' => $isSalesUser,
            'canSell' => $canSell,
            'estimateWizard' => $estimateWizard,
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
