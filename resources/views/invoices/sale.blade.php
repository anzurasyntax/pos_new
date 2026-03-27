<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoiceNo }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; }
        .container { padding: 18px; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; }
        .title { font-size: 20px; font-weight: 700; }
        .sub { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 10px 8px; font-size: 12px; vertical-align: top; }
        th { background: #f9fafb; font-weight: 700; text-align: left; }
        .right { text-align: right; }
        .totals { margin-top: 12px; display: flex; justify-content: flex-end; }
        .totals .box { width: 320px; }
        .row { display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; }
        .row strong { font-size: 14px; }
        .muted { color: #6b7280; }
        .footer { margin-top: 18px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <div class="title">{{ $businessName }}</div>
            <div class="sub">Invoice</div>
        </div>
        <div class="right">
            <div class="title" style="font-size:16px;">{{ $invoiceNo }}</div>
            <div class="sub">Date: {{ $invoiceDate }}</div>
        </div>
    </div>

    <div style="display:flex; gap:12px; margin-top:12px;">
        <div class="box" style="flex:1;">
            <div style="font-weight:700; margin-bottom:6px;">Bill To</div>
            <div>{{ $sale->customer?->name ?? '-' }}</div>
            @if ($sale->customer?->phone)
                <div class="muted">Phone: {{ $sale->customer->phone }}</div>
            @endif
            @if ($sale->customer?->address)
                <div class="muted">Address: {{ $sale->customer->address }}</div>
            @endif
        </div>

        <div class="box" style="flex:1;">
            <div style="font-weight:700; margin-bottom:6px;">Summary</div>
            <div class="row"><span class="muted">Grand Total</span><span>{{ number_format((float) $grandTotal, 2) }}</span></div>
            <div class="row"><span class="muted">Paid</span><span>{{ number_format((float) $paid, 2) }}</span></div>
            <div class="row"><span class="muted">Due</span><span>{{ number_format((float) $due, 2) }}</span></div>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th style="width:40%;">Product</th>
            <th style="width:20%;">Variant</th>
            <th class="right" style="width:10%;">Qty</th>
            <th class="right" style="width:15%;">Price</th>
            <th class="right" style="width:15%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($sale->items as $item)
            @php
                $lineTotal = (int) $item->quantity * (float) $item->price;
            @endphp
            <tr>
                <td>
                    <div style="font-weight:700;">{{ $item->product?->name ?? '-' }}</div>
                    <div class="muted">SKU: {{ $item->product?->sku ?? '-' }}</div>
                </td>
                <td>
                    @if ($item->variant)
                        <div style="font-weight:700;">{{ $item->variant->variant_name }}</div>
                        <div class="muted">SKU: {{ $item->variant->sku ?? '-' }}</div>
                    @else
                        <span class="muted">-</span>
                    @endif
                </td>
                <td class="right">{{ (int) $item->quantity }}</td>
                <td class="right">{{ number_format((float) $item->price, 2) }}</td>
                <td class="right" style="font-weight:700;">{{ number_format($lineTotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="box">
            <div class="row"><span class="muted">Grand Total</span><span>{{ number_format((float) $grandTotal, 2) }}</span></div>
            <div class="row"><span class="muted">Paid</span><span>{{ number_format((float) $paid, 2) }}</span></div>
            <div class="row"><span class="muted">Due</span><span>{{ number_format((float) $due, 2) }}</span></div>
            <div class="footer">
                Generated on {{ now()->format('d M Y') }}.
            </div>
        </div>
    </div>
</div>
</body>
</html>

