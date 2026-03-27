<?php

namespace App\Services;

use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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

