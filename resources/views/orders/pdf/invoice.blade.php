<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice_number }} — {{ $brand_name }}</title>
    <style>
        @page { margin: 14mm 14mm 12mm 14mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #222;
            margin: 0;
            font-size: 13px;
        }
        table { width: 100%; border-collapse: collapse; }
        .toolbar {
            display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 16px;
            position: sticky; top: 0; background: #fff; padding: 8px 0; z-index: 5;
        }
        .toolbar a, .toolbar button {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 12px; border-radius: 8px; border: 0; font-size: 13px;
            font-weight: 600; text-decoration: none; cursor: pointer; color: #fff;
            background: #374151;
        }
        .toolbar .active { background: #2563eb; }
        .toolbar .print { background: #111827; }
        .sheet { max-width: 820px; margin: 0 auto; }
        .top td { vertical-align: top; }
        .pay-box {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 14px 16px;
            width: 48%;
        }
        .pay-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .bank { font-size: 12.5px; line-height: 1.45; margin-bottom: 6px; }
        .bank:last-child { margin-bottom: 0; }
        .head-right { text-align: right; width: 52%; padding-left: 24px; }
        .doc-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0 0 10px;
        }
        .logo { max-height: 72px; max-width: 240px; display: inline-block; }
        .brand-name { font-size: 18px; font-weight: 700; margin: 8px 0 4px; }
        .company-line { color: #4b5563; font-size: 12.5px; line-height: 1.4; }
        .bill {
            margin-top: 28px;
        }
        .bill-left { width: 55%; vertical-align: top; }
        .bill-right { width: 45%; vertical-align: top; }
        .label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .bill-name { font-size: 15px; font-weight: 700; }
        .bill-line { color: #374151; margin-top: 2px; }
        .meta td { padding: 3px 0; }
        .meta .k { color: #6b7280; width: 92px; font-size: 12px; }
        .meta .v { font-weight: 700; text-align: right; }
        .items { margin-top: 22px; }
        .items th {
            text-align: left;
            font-size: 11px;
            letter-spacing: .5px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
        }
        .items th.num, .items td.num { text-align: right; }
        .items td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .item-title { font-weight: 700; }
        .item-detail { color: #6b7280; font-size: 12px; margin-top: 3px; }
        .totals { width: 280px; margin-left: auto; margin-top: 8px; }
        .totals td { padding: 6px 0; }
        .totals .k { color: #6b7280; }
        .totals .v { text-align: right; font-weight: 700; }
        .totals .due td { border-top: 1px solid #e5e7eb; padding-top: 10px; font-size: 15px; }
        @media screen {
            body { background: #f3f4f6; padding: 16px; }
            .sheet { background: #fff; padding: 28px; border-radius: 12px; }
        }
        @media print {
            .toolbar { display: none !important; }
            body { background: #fff; padding: 0; }
            .sheet { max-width: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="print" onclick="window.print()">Cetak</button>
        <a href="{{ $ambulance_url }}" class="{{ $brand_key === 'ambulance' ? 'active' : '' }}">Royal Ambulance</a>
        <a href="{{ $towing_url }}" class="{{ $brand_key === 'towing' ? 'active' : '' }}">Royal Towing</a>
        <a href="{{ $picker_url }}">Pilih kop</a>
    </div>

    <div class="sheet">
        <table class="top">
            <tr>
                <td class="pay-box">
                    <div class="pay-title">Payment instruction</div>
                    @foreach($banks as $bank)
                        <div class="bank">{{ $bank['line'] ?? ($bank['bank'].' '.$bank['number'].' '.$bank['name']) }}</div>
                    @endforeach
                </td>
                <td class="head-right">
                    <div class="doc-title">INVOICE</div>
                    @if($logo_src)
                        <img src="{{ $logo_src }}" class="logo" alt="{{ $brand_name }}">
                    @else
                        <div class="brand-name">{{ $brand_name }}</div>
                    @endif
                    <div class="company-line">{{ $brand_name }}</div>
                    <div class="company-line">{{ $address }}</div>
                    <div class="company-line">{{ $city }}</div>
                    <div class="company-line">{{ $phone }}</div>
                    <div class="company-line">{{ $email }}</div>
                </td>
            </tr>
        </table>

        <table class="bill">
            <tr>
                <td class="bill-left">
                    <div class="label">BILL TO</div>
                    <div class="bill-name">{{ $bill_to_name }}</div>
                    <div class="bill-line">{{ $bill_to_phone }}</div>
                    @if($bill_to_extra)
                        <div class="bill-line">{{ $bill_to_extra }}</div>
                    @endif
                </td>
                <td class="bill-right">
                    <table class="meta">
                        <tr>
                            <td class="k">INVOICE #</td>
                            <td class="v" data-invoice-number="{{ $invoice_number }}">{{ $invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="k">ISSUED</td>
                            <td class="v">{{ $issued_label }}</td>
                        </tr>
                        <tr>
                            <td class="k">DUE</td>
                            <td class="v">{{ $due_label }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th class="num">PRICE</th>
                    <th class="num">QUANTITY</th>
                    <th class="num">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-title">{{ $item_title }}</div>
                        @foreach($item_details as $detail)
                            <div class="item-detail">{{ $detail }}</div>
                        @endforeach
                    </td>
                    <td class="num">{{ \App\Support\InvoiceData::formatMoney($unit_price) }}</td>
                    <td class="num">{{ $quantity }}</td>
                    <td class="num">{{ \App\Support\InvoiceData::formatMoney($amount) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="k">Subtotal</td>
                <td class="v">{{ \App\Support\InvoiceData::formatMoney($amount) }}</td>
            </tr>
            <tr>
                <td class="k">Total</td>
                <td class="v">{{ \App\Support\InvoiceData::formatMoney($amount) }}</td>
            </tr>
            <tr class="due">
                <td class="k">Amount due</td>
                <td class="v">{{ \App\Support\InvoiceData::formatMoney($amount) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
