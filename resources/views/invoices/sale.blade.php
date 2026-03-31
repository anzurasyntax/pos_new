<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoiceNo }} — {{ $businessName }}</title>
    <style>
        @page { margin: 42px 48px 48px 48px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .muted { color: #64748b; }
        .strong { font-weight: 700; color: #0f172a; }
        .right { text-align: right; }
        .center { text-align: center; }
        .upper { text-transform: uppercase; letter-spacing: 0.12em; font-size: 8px; }
        table.flat { border-collapse: collapse; width: 100%; }
        table.flat td { vertical-align: top; }
        .accent-bar {
            height: 4px;
            background: #059669;
            font-size: 0;
            line-height: 0;
        }
        .hero {
            background: #0f172a;
            color: #f8fafc;
            padding: 22px 24px 20px 24px;
        }
        .hero-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0 0 4px 0;
        }
        .hero-tag { font-size: 9px; color: #94a3b8; margin: 0; }
        .doc-label {
            font-size: 11px;
            font-weight: 700;
            color: #34d399;
            letter-spacing: 0.2em;
            margin: 0 0 6px 0;
        }
        .doc-num { font-size: 18px; font-weight: 700; margin: 0; }
        .doc-date { font-size: 9px; color: #94a3b8; margin: 6px 0 0 0; }
        .panel {
            border: 1px solid #e2e8f0;
            border-radius: 2px;
            padding: 12px 14px;
            background: #fafafa;
        }
        .panel-title {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.14em;
            margin: 0 0 8px 0;
        }
        .items-head {
            background: #0f172a;
            color: #f8fafc;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .items-head th { padding: 10px 10px; border: none; }
        .items-body td {
            padding: 11px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        .sku { font-size: 8.5px; color: #64748b; margin-top: 3px; }
        .totals-wrap { width: 280px; margin-left: auto; }
        .totals-row td { padding: 6px 0; font-size: 10px; border: none; }
        .totals-row.total td {
            padding-top: 10px;
            margin-top: 4px;
            border-top: 2px solid #0f172a;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }
        .footer-note {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #64748b;
            line-height: 1.5;
        }
        .thanks {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
        }
    </style>
</head>
<body>

<table class="flat" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="accent-bar">&nbsp;</td>
    </tr>
    <tr>
        <td class="hero">
            <table class="flat" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="58%">
                        <p class="hero-title">{{ $businessName }}</p>
                        <p class="hero-tag">Sales invoice · {{ $currency }}</p>
                    </td>
                    <td width="42%" class="right">
                        <p class="doc-label">Invoice</p>
                        <p class="doc-num">{{ $invoiceNo }}</p>
                        <p class="doc-date">Issued {{ $invoiceDate }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="flat" cellpadding="0" cellspacing="0" style="margin-top: 18px;">
    <tr>
        <td width="50%" style="padding-right: 10px;">
            <div class="panel">
                <p class="panel-title">Issued by</p>
                <p class="strong" style="margin:0 0 6px 0; font-size:11px;">{{ $businessName }}</p>
                @if (filled($issuer['address'] ?? ''))
                    <p class="muted" style="margin:0 0 4px 0; white-space:pre-line;">{{ $issuer['address'] }}</p>
                @endif
                @if (filled($issuer['phone'] ?? ''))
                    <p class="muted" style="margin:0 0 2px 0;">{{ $issuer['phone'] }}</p>
                @endif
                @if (filled($issuer['email'] ?? ''))
                    <p class="muted" style="margin:0 0 2px 0;">{{ $issuer['email'] }}</p>
                @endif
                @if (filled($issuer['tax_id'] ?? ''))
                    <p class="muted" style="margin:4px 0 0 0;">Tax / NTN: {{ $issuer['tax_id'] }}</p>
                @endif
            </div>
        </td>
        <td width="50%" style="padding-left: 10px;">
            <div class="panel">
                <p class="panel-title">Bill to</p>
                <p class="strong" style="margin:0 0 6px 0; font-size:11px;">{{ $sale->customer?->name ?? 'Walk-in customer' }}</p>
                @if ($sale->customer?->phone)
                    <p class="muted" style="margin:0 0 2px 0;">{{ $sale->customer->phone }}</p>
                @endif
                @if ($sale->customer?->address)
                    <p class="muted" style="margin:4px 0 0 0; white-space:pre-line;">{{ $sale->customer->address }}</p>
                @endif
            </div>
        </td>
    </tr>
</table>

<table class="flat" cellpadding="0" cellspacing="0" style="margin-top: 14px;">
    <tr>
        <td width="33%">
            <p class="upper muted" style="margin:0 0 2px 0;">Invoice date</p>
            <p class="strong" style="margin:0; font-size:10px;">{{ $invoiceDate }}</p>
        </td>
        <td width="33%" class="center">
            <p class="upper muted" style="margin:0 0 2px 0;">Reference</p>
            <p class="strong" style="margin:0; font-size:10px;">Sale #{{ $sale->id }}</p>
        </td>
        <td width="33%" class="right">
            <p class="upper muted" style="margin:0 0 2px 0;">Payment</p>
            @php
                $st = $sale->payment_status ?? 'unpaid';
                $pillClass = $st === 'paid' ? 'status-paid' : ($st === 'partial' ? 'status-partial' : 'status-unpaid');
            @endphp
            <span class="status-pill {{ $pillClass }}">{{ $paymentStatusLabel }}</span>
        </td>
    </tr>
</table>

<table class="flat" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
    <thead class="items-head">
        <tr>
            <th width="5%" class="center">#</th>
            <th width="40%">Description</th>
            <th width="18%">Variant</th>
            <th width="9%" class="right">Qty</th>
            <th width="14%" class="right">Unit price</th>
            <th width="14%" class="right">Amount</th>
        </tr>
    </thead>
    <tbody class="items-body">
        @foreach ($sale->items as $index => $item)
            @php
                $lineTotal = (int) $item->quantity * (float) $item->price;
            @endphp
            <tr>
                <td class="center muted">{{ $index + 1 }}</td>
                <td>
                    <span class="strong">{{ $item->product?->name ?? '—' }}</span>
                    <div class="sku">SKU {{ $item->product?->sku ?? '—' }}</div>
                </td>
                <td>
                    @if ($item->variant)
                        <span class="strong">{{ $item->variant->variant_name }}</span>
                        @if ($item->variant->sku)
                            <div class="sku">{{ $item->variant->sku }}</div>
                        @endif
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td class="right strong">{{ (int) $item->quantity }}</td>
                <td class="right">{{ $currency }} {{ number_format((float) $item->price, 2) }}</td>
                <td class="right strong">{{ $currency }} {{ number_format($lineTotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="flat" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 18px;">
    <tr>
        <td align="right">
            <table class="flat totals-wrap" cellpadding="0" cellspacing="0" width="280">
                <tr class="totals-row">
                    <td class="muted">Subtotal</td>
                    <td class="right strong" width="42%">{{ $currency }} {{ number_format((float) $grandTotal, 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td class="muted">Amount paid</td>
                    <td class="right">{{ $currency }} {{ number_format((float) $paid, 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td class="muted">Balance due</td>
                    <td class="right strong" style="color: {{ ($due ?? 0) > 0 ? '#b91c1c' : '#047857' }};">{{ $currency }} {{ number_format((float) $due, 2) }}</td>
                </tr>
                <tr class="totals-row total">
                    <td>Total</td>
                    <td class="right">{{ $currency }} {{ number_format((float) $grandTotal, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="footer-note">
    <p class="thanks">Thank you for your business.</p>
    <p style="margin:0;">
        This document was generated electronically and is valid without a signature.
        @if (filled($issuer['email'] ?? ''))
            Questions? Contact us at {{ $issuer['email'] }}.
        @endif
    </p>
    <p class="muted" style="margin:10px 0 0 0;">Printed {{ now()->format('d M Y, H:i') }} · {{ $businessName }}</p>
</div>

</body>
</html>
