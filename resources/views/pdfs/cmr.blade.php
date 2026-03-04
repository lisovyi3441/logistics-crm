<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CMR Document - {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .col { display: table-cell; border: 1px solid #000; padding: 8px; width: 50%; vertical-align: top; }
        .title { font-weight: bold; font-size: 9px; margin-bottom: 5px; text-transform: uppercase; color: #555; }
        .content { font-size: 11px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INTERNATIONAL CONSIGNMENT NOTE (CMR)</h1>
        <p>Order Reference: <strong>{{ $order->order_number }}</strong></p>
    </div>

    <div class="row">
        <div class="col">
            <div class="title">1. Sender (Name, address, country)</div>
            <div class="content">
                <strong>{{ $order->company->name }}</strong><br>
                {{ $order->pickup_address }}
            </div>
        </div>
        <div class="col">
            <div class="title">2. Consignee (Name, address, country)</div>
            <div class="content">
                <strong>{{ $order->company->name }}</strong><br>
                {{ $order->delivery_address }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="title">3. Place of taking over the goods</div>
            <div class="content">{{ $order->pickup_address }}</div>
        </div>
        <div class="col">
            <div class="title">4. Place of delivery of the goods</div>
            <div class="content">{{ $order->delivery_address }}</div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Marks and Nos.</th>
                <th>Number of packages</th>
                <th>Method of packing</th>
                <th>Nature of the goods</th>
                <th>Gross weight in kg</th>
                <th>Volume in m3 (CBM)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->quantity ?? 1 }}</td>
                <td>Pallet</td>
                <td>{{ $item->name }} {{ $item->is_dangerous ? '(ADR)' : '' }}</td>
                <td>{{ $item->weight_kg }}</td>
                <td>{{ $item->cbm }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL:</td>
                <td style="font-weight: bold;">{{ collect($order->items)->sum('weight_kg') }} kg</td>
                <td style="font-weight: bold;">{{ collect($order->items)->sum('cbm') }} cbm</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; text-align: center; color: #777;">
        <p>This carriage is subject, notwithstanding any clause to the contrary, to the Convention on the Contract for the International Carriage of Goods by Road (CMR).</p>
    </div>
</body>
</html>
