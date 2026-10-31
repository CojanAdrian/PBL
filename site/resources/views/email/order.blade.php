@php($money = fn ($n) => number_format($n, 0, '.', ','))
@php($packs = $order->packs())
<!doctype html>
<html>
<body style="margin:0;background:#f4efe9;font-family:Arial,Helvetica,sans-serif;color:#1b1614">
<div style="max-width:560px;margin:0 auto;padding:24px 16px">
    <div style="background:#0a0908;color:#fff;border-radius:16px 16px 0 0;padding:20px 24px">
        <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#ff6a2b;font-weight:700">New preorder</div>
        <div style="font-size:30px;font-weight:800;letter-spacing:2px;margin-top:4px">{{ $order->code }}</div>
    </div>
    <div style="background:#fff;border-radius:0 0 16px 16px;padding:24px">
        <table style="width:100%;border-collapse:collapse;font-size:15px">
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding:8px 0;border-bottom:1px solid #eee;">{{ \App\Models\Order::describeItem($item) }}</td>
                    <td style="padding:8px 0;border-bottom:1px solid #eee;text-align:right;white-space:nowrap;">{{ $money($item['price'] * $item['qty']) }} {{ $order->currency }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #eee;font-weight:700;">Total ({{ $money($order->total_grams) }} g, {{ $packs }} {{ $packs === 1 ? 'pack' : 'packs' }})</td>
                <td style="padding:8px 0;border-bottom:1px solid #eee;text-align:right;white-space:nowrap;font-weight:700;">{{ $money($order->total_price) }} {{ $order->currency }}</td>
            </tr>
        </table>
        <p style="margin:20px 0 4px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#7a6f67;font-weight:700">Customer</p>
        <p style="margin:0;font-size:15px;line-height:1.6"><strong>{{ $order->name }}</strong><br>
            Phone: <a href="tel:{{ $order->phone }}" style="color:#c71e18">{{ $order->phone }}</a><br>
            @if ($order->email)
                Email: <a href="mailto:{{ $order->email }}" style="color:#c71e18">{{ $order->email }}</a><br>
            @endif
            Delivery
            @if ($order->wanted_date)
                <br>Wanted date: {{ $order->wanted_date->format('Y-m-d') }}
            @endif
        </p>
        @if ($order->note)
            <p style="margin:16px 0 0;padding:12px 14px;background:#f4efe9;border-radius:10px;font-size:14px"><strong>Note:</strong> {{ $order->note }}</p>
        @endif
        <p style="margin:24px 0 0"><a href="{{ $adminUrl }}" style="display:inline-block;background:#c71e18;color:#fff;text-decoration:none;font-weight:700;padding:12px 22px;border-radius:999px">Open orders</a></p>
    </div>
</div>
</body>
</html>
