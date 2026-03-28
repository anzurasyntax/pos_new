<?php

namespace App\Services;

use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Sales report aggregated by product + variant.
     *
     * Filters supported (all optional):
     * - date_from (Y-m-d)
     * - date_to (Y-m-d)
     * - customer_id
     * - product_id
     */
    public function salesReport(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'sale_items.variant_id', '=', 'product_variants.id')
            ->selectRaw('products.name as product_name')
            ->addSelect(DB::raw('product_variants.variant_name as variant_name'))
            ->addSelect(DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->addSelect(DB::raw('SUM(sale_items.quantity * sale_items.price) as total_amount'))
            ->groupBy('products.name', 'product_variants.variant_name');

        $query = $this->applySalesDateFilters($query, $filters);

        if (! empty($filters['customer_id'])) {
            $query->where('sales.customer_id', (int) $filters['customer_id']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('sale_items.product_id', (int) $filters['product_id']);
        }

        return $query
            ->orderByDesc('total_amount')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function salesReportTotals(array $filters): array
    {
        $grandTotal = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('SUM(sale_items.quantity * sale_items.price) as total_amount');

        $grandTotal = $this->applySalesDateFilters($grandTotal, $filters);

        if (! empty($filters['customer_id'])) {
            $grandTotal->where('sales.customer_id', (int) $filters['customer_id']);
        }

        if (! empty($filters['product_id'])) {
            $grandTotal->where('sale_items.product_id', (int) $filters['product_id']);
        }

        return [
            'total_amount' => (float) ($grandTotal->value('total_amount') ?? 0),
        ];
    }

    /**
     * Purchase report aggregated by product + variant.
     */
    public function purchaseReport(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'purchase_items.variant_id', '=', 'product_variants.id')
            ->selectRaw('products.name as product_name')
            ->addSelect(DB::raw('product_variants.variant_name as variant_name'))
            ->addSelect(DB::raw('SUM(purchase_items.quantity) as total_qty'))
            ->addSelect(DB::raw('SUM(purchase_items.quantity * purchase_items.price) as total_amount'))
            ->groupBy('products.name', 'product_variants.variant_name');

        $query = $this->applyPurchasesDateFilters($query, $filters);

        if (! empty($filters['supplier_id'])) {
            $query->where('purchases.supplier_id', (int) $filters['supplier_id']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('purchase_items.product_id', (int) $filters['product_id']);
        }

        return $query
            ->orderByDesc('total_amount')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function purchaseReportTotals(array $filters): array
    {
        $grandTotal = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->selectRaw('SUM(purchase_items.quantity * purchase_items.price) as total_amount');

        $grandTotal = $this->applyPurchasesDateFilters($grandTotal, $filters);

        if (! empty($filters['supplier_id'])) {
            $grandTotal->where('purchases.supplier_id', (int) $filters['supplier_id']);
        }

        if (! empty($filters['product_id'])) {
            $grandTotal->where('purchase_items.product_id', (int) $filters['product_id']);
        }

        return [
            'total_amount' => (float) ($grandTotal->value('total_amount') ?? 0),
        ];
    }

    /**
     * Profit & Loss for a date range:
     * Profit = Total Sales - Total Purchases - Total Expenses
     */
    public function profitAndLoss(array $filters): array
    {
        $sales = Sale::query()->when(
            ! empty($filters['date_from']) || ! empty($filters['date_to']),
            function ($q) use ($filters) {
                $this->applySalesDateConstraintsEloquent($q, $filters);
            }
        )->sum('total_amount');

        $purchases = Purchase::query()->when(
            ! empty($filters['date_from']) || ! empty($filters['date_to']),
            function ($q) use ($filters) {
                $this->applyPurchasesDateConstraintsEloquent($q, $filters);
            }
        )->sum('total_amount');

        $expenses = Expense::query()->when(
            ! empty($filters['date_from']) || ! empty($filters['date_to']),
            function ($q) use ($filters) {
                $this->applyExpensesDateConstraintsEloquent($q, $filters);
            }
        )->sum('amount');

        $sales = (float) $sales;
        $purchases = (float) $purchases;
        $expenses = (float) $expenses;

        return [
            'total_sales' => $sales,
            'total_purchases' => $purchases,
            'total_expenses' => $expenses,
            'profit' => $sales - $purchases - $expenses,
        ];
    }

    public function expenseReport(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Expense::query()->orderByDesc('date');

        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('account_id', (int) $filters['account_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function expenseReportTotals(array $filters): array
    {
        $query = Expense::query();

        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('account_id', (int) $filters['account_id']);
        }

        return [
            'total_amount' => (float) ($query->sum('amount') ?? 0),
        ];
    }

    public function customerLedgerReport(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = CustomerLedger::query()
            ->orderByDesc('created_at');

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', (int) $filters['customer_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function customerLedgerReportTotals(array $filters): array
    {
        $query = CustomerLedger::query();

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', (int) $filters['customer_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_debit' => (float) ($query->sum('debit') ?? 0),
            'total_credit' => (float) ($query->sum('credit') ?? 0),
        ];
    }

    /**
     * Single-day sales totals (count + amount).
     *
     * @return array{date: string, sale_count: int, total_amount: float}
     */
    public function dailySalesReport(string $date): array
    {
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->copy()->endOfDay();

        $row = DB::table('sales')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as sale_count, COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        return [
            'date' => $date,
            'sale_count' => (int) ($row->sale_count ?? 0),
            'total_amount' => (float) ($row->total_amount ?? 0),
        ];
    }

    /**
     * Calendar month sales totals.
     *
     * @return array{year: int, month: int, sale_count: int, total_amount: float}
     */
    public function monthlySalesReport(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        $row = DB::table('sales')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as sale_count, COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        return [
            'year' => $year,
            'month' => $month,
            'sale_count' => (int) ($row->sale_count ?? 0),
            'total_amount' => (float) ($row->total_amount ?? 0),
        ];
    }

    /**
     * Gross profit from sales using line COGS (variant or product purchase_price).
     *
     * @return array{revenue: float, cost_of_goods_sold: float, gross_profit: float}
     */
    public function profitEstimation(array $filters): array
    {
        $query = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'sale_items.variant_id', '=', 'product_variants.id');

        if (! empty($filters['date_from'])) {
            $query->where('sales.created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('sales.created_at', '<=', $filters['date_to']);
        }

        $row = $query->selectRaw(
            'COALESCE(SUM(sale_items.quantity * sale_items.price), 0) as revenue, '.
            'COALESCE(SUM(sale_items.quantity * COALESCE(product_variants.purchase_price, products.purchase_price)), 0) as cogs'
        )->first();

        $revenue = (float) ($row->revenue ?? 0);
        $cogs = (float) ($row->cogs ?? 0);

        return [
            'revenue' => $revenue,
            'cost_of_goods_sold' => $cogs,
            'gross_profit' => round($revenue - $cogs, 2),
        ];
    }

    public function profitEstimationForDateRange(DateTimeInterface $from, DateTimeInterface $to): float
    {
        $result = $this->profitEstimation([
            'date_from' => $from,
            'date_to' => $to,
        ]);

        return $result['gross_profit'];
    }

    /**
     * Inventory valuation: variant stock at variant purchase_price + simple products at product purchase_price.
     *
     * @return array{total_valuation: float, variants_valuation: float, simple_products_valuation: float}
     */
    public function stockValuationReport(): array
    {
        $variantsValuation = (float) (DB::table('product_variants')
            ->selectRaw('COALESCE(SUM(stock_quantity * purchase_price), 0) as v')
            ->value('v') ?? 0);

        $simpleValuation = (float) (DB::table('products as p')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw('1'))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'p.id');
            })
            ->selectRaw('COALESCE(SUM(p.stock_quantity * p.purchase_price), 0) as v')
            ->value('v') ?? 0);

        return [
            'total_valuation' => round($variantsValuation + $simpleValuation, 2),
            'variants_valuation' => round($variantsValuation, 2),
            'simple_products_valuation' => round($simpleValuation, 2),
        ];
    }

    /**
     * Top sellers by quantity (variant granularity when applicable).
     *
     * @return Collection<int, object>
     */
    public function topSellingProducts(int $limit = 10, array $filters = []): Collection
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'sale_items.variant_id', '=', 'product_variants.id')
            ->selectRaw(
                'products.id as product_id, products.name as product_name, '.
                'product_variants.variant_name as variant_name, '.
                'SUM(sale_items.quantity) as total_qty, '.
                'SUM(sale_items.quantity * sale_items.price) as total_revenue'
            )
            ->groupBy('products.id', 'products.name', 'product_variants.variant_name')
            ->orderByDesc('total_qty')
            ->limit($limit);

        if (! empty($filters['date_from'])) {
            $query->where('sales.created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('sales.created_at', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Variant rows below threshold; includes simple (non-variant) products as one row each.
     *
     * @return Collection<int, object>
     */
    public function lowStockReportVariantBased(int $defaultThreshold = 5): Collection
    {
        $variants = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->whereRaw('pv.stock_quantity <= COALESCE(pv.low_stock_threshold, ?)', [$defaultThreshold])
            ->selectRaw(
                'p.name as product_name, pv.variant_name, pv.sku as variant_sku, p.sku as product_sku, '.
                'pv.stock_quantity, COALESCE(pv.low_stock_threshold, ?) as threshold',
                [$defaultThreshold]
            )
            ->orderBy('pv.stock_quantity')
            ->get();

        $simple = DB::table('products as p')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw('1'))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'p.id');
            })
            ->where('p.stock_quantity', '<=', $defaultThreshold)
            ->selectRaw(
                'p.name as product_name, NULL as variant_name, NULL as variant_sku, p.sku as product_sku, '.
                'p.stock_quantity, ? as threshold',
                [$defaultThreshold]
            )
            ->orderBy('p.stock_quantity')
            ->get();

        return $variants->concat($simple);
    }

    /**
     * Count of variant SKUs + simple products at or below low-stock threshold.
     */
    public function lowStockItemsCount(int $defaultThreshold = 5): int
    {
        $variantCount = DB::table('product_variants')
            ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, ?)', [$defaultThreshold])
            ->count();

        $simpleCount = DB::table('products as p')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw('1'))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'p.id');
            })
            ->where('p.stock_quantity', '<=', $defaultThreshold)
            ->count();

        return (int) $variantCount + (int) $simpleCount;
    }

    private function applySalesDateFilters($query, array $filters)
    {
        if (! empty($filters['date_from'])) {
            $query->where('sales.created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('sales.created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function applyPurchasesDateFilters($query, array $filters)
    {
        if (! empty($filters['date_from'])) {
            $query->where('purchases.created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('purchases.created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function applySalesDateConstraintsEloquent($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
    }

    private function applyPurchasesDateConstraintsEloquent($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
    }

    private function applyExpensesDateConstraintsEloquent($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }
    }
}
