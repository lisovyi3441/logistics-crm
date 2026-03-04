<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; padding: 30px; color: #333; }
        .header { display: table; width: 100%; margin-bottom: 40px; border-bottom: 1px solid #ddd; padding-bottom: 20px; }
        .col-left { display: table-cell; width: 50%; vertical-align: top; }
        .col-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        h1 { color: #1e3a8a; margin: 0 0 10px 0; font-size: 28px; }
        .invoice-details { margin-top: 15px; font-size: 13px; }
        .invoice-details span { font-weight: bold; color: #555; display: inline-block; width: 100px; text-align: left;}
        
        .parties { display: table; width: 100%; margin-bottom: 40px; }
        .party { display: table-cell; width: 50%; padding: 15px; background: #f9fafb; border-radius: 4px; }
        .party h3 { margin-top: 0; color: #4b5563; font-size: 14px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items th, .items td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .items th { background-color: #f3f4f6; text-align: left; font-weight: bold; color: #4b5563; }
        .items td.amount { text-align: right; }
        .items th.amount { text-align: right; }

        .totals { display: table; width: 100%; }
        .totals-left { display: table-cell; width: 50%; }
        .totals-right { display: table-cell; width: 50%; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 8px; text-align: right; border-bottom: 1px dotted #e5e7eb; }
        .totals-table td.label { color: #6b7280; text-align: left; }
        .totals-table tr.grand-total td { font-weight: bold; font-size: 16px; color: #111827; border-bottom: none; border-top: 2px solid #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="col-left">
            <h1>INVOICE</h1>
            <div style="font-size: 18px; color: #6b7280;"># {{ $order->order_number }}</div>
        </div>
        <div class="col-right">
            <h2>{{ config('app.name', 'Logistics Platform') }}</h2>
            <p>{{ config('app.url', 'http://localhost') }}</p>
        </div>
    </div>

    <div class="parties">
        <div class="party" style="border-right: 15px solid #fff;">
            <h3>Billed To (Customer)</h3>
            <strong>{{ $order->company->name }}</strong><br>
            VAT ID: {{ $order->company->vat_number ?? 'N/A' }}<br>
            {{ $order->company->address ?? 'Address not provided' }}
        </div>
        <div class="party">
            <h3>Invoice Details</h3>
            <div class="invoice-details">
                <div><span>Date:</span> {{ now()->format('M d, Y') }}</div>
                <div><span>Order Ref:</span> {{ $order->order_number }}</div>
                <div><span>Due Date:</span> {{ now()->addDays(14)->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Qty</th>
                <th class="amount">Unit Price</th>
                <th class="amount">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Freight Transportation Services</strong><br>
                    <span style="color: #6b7280; font-size: 11px;">
                        Route: {{ $order->pickup_address ?? 'Origin' }} to {{ $order->delivery_address ?? 'Destination' }}
                    </span>
                </td>
                <td class="amount">1</td>
                <td class="amount">{{ number_format($order->base_price_cents / 100, 2) }} {{ $order->currency }}</td>
                <td class="amount">{{ number_format($order->base_price_cents / 100, 2) }} {{ $order->currency }}</td>
            </tr>
            @if($order->insurance_fee_cents > 0)
            <tr>
                <td>Cargo Insurance Premium</td>
                <td class="amount">1</td>
                <td class="amount">{{ number_format($order->insurance_fee_cents / 100, 2) }} {{ $order->currency }}</td>
                <td class="amount">{{ number_format($order->insurance_fee_cents / 100, 2) }} {{ $order->currency }}</td>
            </tr>
            @endif
            @if($order->surcharge_cents > 0)
            <tr>
                <td>ADR / Hazard Surcharges</td>
                <td class="amount">1</td>
                <td class="amount">{{ number_format($order->surcharge_cents / 100, 2) }} {{ $order->currency }}</td>
                <td class="amount">{{ number_format($order->surcharge_cents / 100, 2) }} {{ $order->currency }}</td>
            </tr>
            @endif
            @if($order->discount_cents > 0)
            <tr>
                <td>Volume Discount</td>
                <td class="amount">1</td>
                <td class="amount">-{{ number_format($order->discount_cents / 100, 2) }} {{ $order->currency }}</td>
                <td class="amount">-{{ number_format($order->discount_cents / 100, 2) }} {{ $order->currency }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-left">
            <h4 style="color: #4b5563; margin-top: 0;">Payment Instructions</h4>
            <p style="font-size: 11px; color: #6b7280; line-height: 1.5;">
                Please remit payment within 14 days.<br>
                Payment details are subject to the terms of your service contract.<br><br>
                Include Invoice #{{ $order->order_number }} in transfer description.
            </p>
        </div>
        <div class="totals-right">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal</td>
                    <td>{{ number_format(($order->base_price_cents + $order->insurance_fee_cents + $order->surcharge_cents - $order->discount_cents) / 100, 2) }} {{ $order->currency }}</td>
                </tr>
                <tr>
                    <td class="label">Tax (VAT 20%)</td>
                    <td>{{ number_format($order->tax_cents / 100, 2) }} {{ $order->currency }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="label">Total Due</td>
                    <td>{{ number_format($order->total_price_cents / 100, 2) }} {{ $order->currency }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
