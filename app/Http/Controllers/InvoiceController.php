<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function invoice(Sale $sale)
    {
        $sale->load([
            'customer',
            'items.product',
            'items.variant',
        ]);

        $invoiceNo = 'INV-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
        $invoiceDate = $sale->created_at?->format('d M Y');
        $currency = env('INVOICE_CURRENCY_LABEL', 'PKR');

        $data = [
            'businessName' => config('app.name', 'Shop System'),
            'issuer' => [
                'address' => env('INVOICE_BUSINESS_ADDRESS', ''),
                'phone' => env('INVOICE_BUSINESS_PHONE', ''),
                'email' => env('INVOICE_BUSINESS_EMAIL', ''),
                'tax_id' => env('INVOICE_TAX_ID', ''),
            ],
            'invoiceNo' => $invoiceNo,
            'invoiceDate' => $invoiceDate,
            'sale' => $sale,
            'paid' => (float) $sale->paid_amount,
            'due' => (float) $sale->due_amount,
            'grandTotal' => (float) $sale->total_amount,
            'currency' => $currency,
            'paymentStatusLabel' => match ($sale->payment_status ?? 'unpaid') {
                'paid' => 'Paid in full',
                'partial' => 'Partially paid',
                default => 'Unpaid',
            },
        ];

        $pdf = Pdf::loadView('invoices.sale', $data)->setPaper('a4', 'portrait');

        return $pdf->download('invoice-'.$invoiceNo.'.pdf');
    }
}
