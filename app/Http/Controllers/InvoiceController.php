<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function invoice(Sale $sale)
    {
        $sale->load([
            'customer',
            'items.product',
            'items.variant',
        ]);

        $invoiceNo = 'INV-'.$sale->id;
        $invoiceDate = $sale->created_at?->format('Y-m-d');

        $data = [
            'businessName' => config('app.name', 'Shop System'),
            'invoiceNo' => $invoiceNo,
            'invoiceDate' => $invoiceDate,
            'sale' => $sale,
            'paid' => (float) $sale->paid_amount,
            'due' => (float) $sale->due_amount,
            'grandTotal' => (float) $sale->total_amount,
        ];

        $pdf = Pdf::loadView('invoices.sale', $data)->setPaper('a4', 'portrait');

        return $pdf->download('invoice-'.$invoiceNo.'.pdf');
    }
}

