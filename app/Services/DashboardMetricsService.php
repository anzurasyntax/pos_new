<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /**
     * Aggregated dashboard metrics for the current day (app timezone).
     *
     * @return array{
     *     total_sales_today: float,
     *     total_purchases_today: float,
     *     total_profit_today: float,
     *     low_stock_items_count: int,
     *     total_customers_credit_balance: float
     * }
     */
    public function todaySummary(): array
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        $totalSalesToday = (float) Sale::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        $totalPurchasesToday = (float) Purchase::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        $profitToday = $this->reportService->profitEstimationForDateRange($start, $end);

        $lowStockCount = $this->reportService->lowStockItemsCount();

        $creditBalance = $this->sumLatestCustomerBalances();

        return [
            'total_sales_today' => $totalSalesToday,
            'total_purchases_today' => $totalPurchasesToday,
            'total_profit_today' => $profitToday,
            'low_stock_items_count' => $lowStockCount,
            'total_customers_credit_balance' => $creditBalance,
        ];
    }

    /**
     * Sum of latest running balance per customer (one row per customer from customer_ledgers).
     */
    private function sumLatestCustomerBalances(): float
    {
        $latest = DB::table('customer_ledgers')
            ->selectRaw('customer_id, MAX(id) as last_id')
            ->groupBy('customer_id');

        $sum = DB::query()
            ->fromSub($latest, 'latest')
            ->join('customer_ledgers as cl', 'cl.id', '=', 'latest.last_id')
            ->where('cl.balance', '>', 0)
            ->sum('cl.balance');

        return (float) ($sum ?? 0);
    }
}
