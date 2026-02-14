<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Receipt #{{ $order->id }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Courier New', monospace; font-size:11px; line-height:1.4; width:70mm; padding:5mm; }
        .header, .footer { text-align:center; }
        .header h1 { font-size:16px; font-weight:bold; }
        .section { margin-bottom:10px; border-bottom:1px dashed #000; padding-bottom:5px; }
        .info-row { display:flex; justify-content:space-between; margin:2px 0; }
        .info-label { font-weight:bold; width:45%; }
        .info-value { width:55%; text-align:right; }
        table { width:100%; border-collapse: collapse; margin-top:5px; }
        th, td { font-size:10px; padding:3px 0; }
        th { border-bottom:1px solid #000; text-align:left; }
        .item-name { width:50%; }
        .item-qty { width:15%; text-align:center; }
        .item-price { width:35%; text-align:right; }
        .total-section { margin-top:10px; border-top:2px solid #000; padding-top:5px; }
        .total-row { display:flex; justify-content:space-between; margin:2px 0; }
        .grand-total { font-weight:bold; font-size:12px; }
        .status-badge { font-size:10px; font-weight:bold; padding:2px 5px; border:1px solid #000; display:inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>ORDER RECEIPT</p>
        @if(config('settings.store_address'))<p>{{ config('settings.store_address') }}</p>@endif
        @if(config('settings.store_phone'))<p>Tel: {{ config('settings.store_phone') }}</p>@endif
    </div>

    <!-- Order Info -->
    <div class="section">
        <div class="info-row"><span class="info-label">Receipt #:</span><span class="info-value">{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span></div>
        <div class="info-row"><span class="info-label">Date:</span><span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="info-row"><span class="info-label">Status:</span>
            <span class="info-value">
                <span class="status-badge">
                    @php
                        $received = $order->receivedAmount();
                        $total = $order->total();
                        if($received == 0) echo 'NOT PAID';
                        elseif($received < $total) echo 'PARTIAL';
                        else echo 'PAID';
                    @endphp
                </span>
            </span>
        </div>
        <div class="info-row"><span class="info-label">Customer:</span><span class="info-value">{{ $order->getCustomerName() }}</span></div>
    </div>

    <!-- Items -->
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th class="item-name">Item</th>
                    <th class="item-qty">Qty</th>
                    <th class="item-price">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="item-name">{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="item-qty">{{ $item->quantity }}</td>
                        <td class="item-price">{{ config('settings.currency_symbol') }}{{ number_format($item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="total-section">
        <div class="total-row"><span>Subtotal:</span><span>{{ config('settings.currency_symbol') }}{{ number_format($order->total(), 2) }}</span></div>
        <div class="total-row"><span>Paid:</span><span>{{ config('settings.currency_symbol') }}{{ number_format($order->receivedAmount(), 2) }}</span></div>
        <div class="total-row grand-total"><span>Balance:</span><span>{{ config('settings.currency_symbol') }}{{ number_format($order->total() - $order->receivedAmount(), 2) }}</span></div>
        <div class="total-row"><span>Total Items:</span><span>{{ $order->items->sum('quantity') }} pcs</span></div>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        <p style="font-size:9px;">This is a computer-generated receipt. No signature required.</p>
    </div>
</body>
</html>
