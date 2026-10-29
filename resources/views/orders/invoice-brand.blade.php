<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih kop invoice {{ $order->order_number }}</title>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: #f3f4f6;
            margin: 0;
            color: #111827;
        }
        .wrap { max-width: 720px; margin: 40px auto; padding: 0 16px; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        p { color: #4b5563; margin: 0 0 24px; }
        .grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
        @media (min-width: 640px) { .grid { grid-template-columns: 1fr 1fr; } }
        a.card {
            display: block;
            background: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 22px 20px;
            text-decoration: none;
            color: inherit;
            text-align: center;
        }
        a.card:hover { border-color: #2563eb; }
        a.card.suggested { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .card img { max-height: 72px; max-width: 100%; display: block; margin: 0 auto 12px; }
        .card .name { font-size: 18px; font-weight: 700; }
        .card .hint { font-size: 12px; color: #6b7280; margin-top: 6px; }
        .back { display: inline-block; margin-top: 22px; color: #2563eb; font-size: 14px; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Cetak invoice</h1>
        <p>Pesanan {{ $order->order_number }}. Pilih kop surat — satu pesanan satu invoice.</p>
        <div class="grid">
            <a class="card {{ $suggested === 'ambulance' ? 'suggested' : '' }}" href="{{ route('orders.invoice', [$order, 'brand' => 'ambulance']) }}">
                <img src="{{ asset('images/invoice/royal-ambulance.png') }}" alt="Royal Ambulance">
                <div class="name">Royal Ambulance</div>
                <div class="hint">Logo, nama, dan rekening Ambulance</div>
            </a>
            <a class="card {{ $suggested === 'towing' ? 'suggested' : '' }}" href="{{ route('orders.invoice', [$order, 'brand' => 'towing']) }}">
                <img src="{{ asset('images/invoice/royal-towing.png') }}" alt="Royal Towing">
                <div class="name">Royal Towing</div>
                <div class="hint">Logo, nama, dan rekening Towing</div>
            </a>
        </div>
        <a class="back" href="{{ route('orders.show', $order) }}">Kembali ke pesanan</a>
    </div>
</body>
</html>
