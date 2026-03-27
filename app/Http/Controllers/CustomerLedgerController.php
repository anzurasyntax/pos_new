<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use Illuminate\Http\Request;

class CustomerLedgerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedCustomerId = (int) $request->query('customer_id', $customers->first()?->id ?? 0);

        $ledgerEntries = collect();
        $currentBalance = 0.0;

        if ($selectedCustomerId > 0) {
            $ledgerEntries = CustomerLedger::query()
                ->where('customer_id', $selectedCustomerId)
                ->orderBy('created_at')
                ->get();

            $last = $ledgerEntries->last();
            $currentBalance = $last?->balance ?? 0.0;
        }

        return view('customers', [
            'customers' => $customers,
            'selectedCustomerId' => $selectedCustomerId,
            'ledgerEntries' => $ledgerEntries,
            'currentBalance' => $currentBalance,
        ]);
    }
}
