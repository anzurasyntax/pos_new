<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddPurchasePaymentRequest;
use App\Http\Requests\AddSalePaymentRequest;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {
    }

    public function storeSalePayment(AddSalePaymentRequest $request, Sale $sale): JsonResponse
    {
        $payment = $this->paymentService->addSalePayment(
            $sale,
            (float) $request->input('amount'),
            (string) $request->input('method'),
            (int) $request->input('account_id'),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'sale' => $sale->fresh(),
        ]);
    }

    public function storePurchasePayment(AddPurchasePaymentRequest $request, Purchase $purchase): JsonResponse
    {
        $payment = $this->paymentService->addPurchasePayment(
            $purchase,
            (float) $request->input('amount'),
            (string) $request->input('method'),
            (int) $request->input('account_id'),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'payment' => $payment,
            'purchase' => $purchase->fresh(),
        ]);
    }

    public function index(): View
    {
        $payments = Payment::query()
            ->with('account')
            ->orderByDesc('created_at')
            ->paginate(20);

        $saleClass = Sale::class;
        $purchaseClass = Purchase::class;

        $saleIds = $payments->where('payable_type', $saleClass)->pluck('payable_id')->unique()->values()->all();
        $purchaseIds = $payments->where('payable_type', $purchaseClass)->pluck('payable_id')->unique()->values()->all();

        $sales = Sale::query()->whereIn('id', $saleIds)->with('customer')->get()->keyBy('id');
        $purchases = Purchase::query()->whereIn('id', $purchaseIds)->with('supplier')->get()->keyBy('id');

        $payableNames = [];
        $payableTypes = [];
        foreach ($payments as $payment) {
            if ($payment->payable_type === $saleClass) {
                $sale = $sales->get((int) $payment->payable_id);
                $payableNames[$payment->id] = $sale?->customer?->name ?? '-';
                $payableTypes[$payment->id] = 'Sale';
            } elseif ($payment->payable_type === $purchaseClass) {
                $purchase = $purchases->get((int) $payment->payable_id);
                $payableNames[$payment->id] = $purchase?->supplier?->name ?? '-';
                $payableTypes[$payment->id] = 'Purchase';
            } else {
                $payableNames[$payment->id] = '-';
                $payableTypes[$payment->id] = '-';
            }
        }

        return view('payments.index', [
            'payments' => $payments,
            'payableNames' => $payableNames,
            'payableTypes' => $payableTypes,
        ]);
    }
}

