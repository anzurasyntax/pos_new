<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }

    public function sales(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'customer_id', 'product_id']);

        $sales = $this->reportService->salesReport($filters, 20);
        $totals = $this->reportService->salesReportTotals($filters);

        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('reports.sales', [
            'filters' => $filters,
            'sales' => $sales,
            'totals' => $totals,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    public function purchases(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'supplier_id', 'product_id']);

        $purchases = $this->reportService->purchaseReport($filters, 20);
        $totals = $this->reportService->purchaseReportTotals($filters);

        $suppliers = Supplier::query()->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('reports.purchases', [
            'filters' => $filters,
            'purchases' => $purchases,
            'totals' => $totals,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function profitLoss(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'customer_id', 'product_id']);

        $result = $this->reportService->profitAndLoss($filters);

        return view('reports.profit-loss', [
            'filters' => $filters,
            'result' => $result,
        ]);
    }

    public function expenses(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'account_id']);
        $expenses = $this->reportService->expenseReport($filters, 20);
        $totals = $this->reportService->expenseReportTotals($filters);

        $accounts = \App\Models\Account::query()
            ->where('type', 'expense')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('reports.expenses', [
            'filters' => $filters,
            'expenses' => $expenses,
            'totals' => $totals,
            'accounts' => $accounts,
        ]);
    }

    public function customerLedger(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'customer_id']);
        $ledger = $this->reportService->customerLedgerReport($filters, 20);
        $totals = $this->reportService->customerLedgerReportTotals($filters);

        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.customer-ledger', [
            'filters' => $filters,
            'ledger' => $ledger,
            'totals' => $totals,
            'customers' => $customers,
        ]);
    }
}

