<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        $brand_blue = $brand_blue ?? '#4CA7DC';
        $cars = $cars ?? [];
    @endphp
    <title>Surat Jalan {{ $nomor }}</title>
    <style>
        @page { margin: 10mm 11mm 10mm 11mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
        }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo-img { width: 132px; height: auto; display: block; }
        .logo-box { font-family: Times-Roman, Times, serif; line-height: 0.95; }
        .logo-top { font-size: 20px; font-weight: bold; }
        .logo-bottom { font-size: 15px; font-weight: bold; }
        .company { text-align: right; }
        .company-name {
            font-size: 12px;
            font-weight: bold;
            color: {{ $brand_blue }};
        }
        .company-line { color: #555; font-size: 9px; margin-top: 1px; }
        .rule {
            border: 0;
            border-top: 1.5px solid {{ $brand_blue }};
            margin: 8px 0 6px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            color: {{ $brand_blue }};
            margin: 2px 0 8px;
        }
        .meta td { padding: 1px 0; }
        .meta .label { width: 48px; }
        .heading {
            font-size: 11px;
            font-weight: bold;
            color: {{ $brand_blue }};
            letter-spacing: 0.4px;
            margin: 8px 0 4px;
        }
        .heading-row td { vertical-align: bottom; padding: 0; }
        .heading-row .cars { text-align: right; width: 200px; }
        .heading-row .cars img { height: 38px; width: auto; }
        .fields td { padding: 3px 4px 3px 0; vertical-align: top; }
        .fields .lbl { width: 26%; color: #555; }
        .fields .sep { width: 8px; color: #555; }
        .split > tbody > tr > td { width: 50%; vertical-align: top; }
        .split .pad-left { padding-right: 10px; }
        .split .pad-right { padding-left: 10px; }
        .equip-wrap > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
        .equip-wrap .gap { width: 10px; }
        .equip { width: 100%; font-size: 8px; table-layout: fixed; }
        .equip th, .equip td {
            border: 1px solid #d9d9d9;
            padding: 2.5px 5px;
            vertical-align: middle;
        }
        .equip th {
            background: {{ $brand_blue }};
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .equip th.item { text-align: left; width: 68%; }
        .equip td.item { width: 68%; }
        .equip .mark { width: 16%; text-align: center; }
        .equip tr.alt td { background: #f6f6f6; }
        .equip tr.alt td.mark { background: #f6f6f6; }
        .cars-grid td {
            width: 50%;
            text-align: center;
            vertical-align: middle;
            padding: 2px 10px 4px;
        }
        .cars-grid img { height: 62px; width: auto; max-width: 95%; }
        .car-label {
            font-size: 7px;
            color: #777;
            margin-top: 1px;
        }
        .notes {
            min-height: 22px;
            padding: 4px 2px;
            white-space: pre-wrap;
            border-bottom: 1px solid #ddd;
        }
        .sign { margin-top: 10px; }
        .sign td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sign-role { font-weight: bold; color: {{ $brand_blue }}; }
        .sign-space { height: 32px; }
        .sign-line {
            border-top: 1px solid #333;
            margin: 0 16px;
            padding-top: 3px;
            font-size: 8px;
            color: #666;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7.5px;
            color: #888;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 150px;">
                @if(!empty($logo_src))
                    <img src="{{ $logo_src }}" class="logo-img" alt="ROYAL TOWING">
                @else
                    <div class="logo-box">
                        <div class="logo-top">{{ $brand_top }}</div>
                        <div class="logo-bottom">{{ $brand_bottom }}</div>
                    </div>
                @endif
            </td>
            <td class="company">
                <div class="company-name">{{ $company_name }}</div>
                <div class="company-line">{{ $company_phone }}</div>
                <div class="company-line">{{ $company_email }}</div>
            </td>
        </tr>
    </table>

    <hr class="rule">
    <div class="title">SURAT JALAN</div>

    <table class="meta">
        <tr>
            <td class="label">No. SJ</td>
            <td>: {{ $nomor }}</td>
            <td style="text-align: right;">Tanggal : {{ $tanggal }}</td>
        </tr>
    </table>

    <div class="heading">DATA KENDARAAN</div>
    <table class="fields">
        <tr>
            <td class="lbl">Jenis Kendaraan</td>
            <td class="sep">:</td>
            <td>{{ $jenis_kendaraan }}</td>
            <td class="lbl">No. Polisi</td>
            <td class="sep">:</td>
            <td>{{ $nopol }}</td>
        </tr>
        <tr>
            <td class="lbl">Driver</td>
            <td class="sep">:</td>
            <td>{{ $driver }}</td>
            <td class="lbl">Asisten</td>
            <td class="sep">:</td>
            <td>{{ $asisten }}</td>
        </tr>
    </table>

    <table class="split">
        <tr>
            <td class="pad-left">
                <div class="heading">PENGIRIM</div>
                <table class="fields">
                    <tr>
                        <td class="lbl">Nama</td>
                        <td class="sep">:</td>
                        <td>{{ $pengirim_nama }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Telepon</td>
                        <td class="sep">:</td>
                        <td>{{ $pengirim_telepon }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Alamat</td>
                        <td class="sep">:</td>
                        <td>{{ $pengirim_alamat }}</td>
                    </tr>
                </table>
            </td>
            <td class="pad-right">
                <div class="heading">PENERIMA</div>
                <table class="fields">
                    <tr>
                        <td class="lbl">Nama</td>
                        <td class="sep">:</td>
                        <td>{{ $penerima_nama }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Telepon</td>
                        <td class="sep">:</td>
                        <td>{{ $penerima_telepon }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Alamat</td>
                        <td class="sep">:</td>
                        <td>{{ $penerima_alamat }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="heading-row">
        <tr>
            <td><div class="heading" style="margin: 10px 0 6px;">PERLENGKAPAN KENDARAAN</div></td>
            <td class="cars">
                @if(!empty($cars['rear_front']))
                    <img src="{{ $cars['rear_front'] }}" alt="Denah mobil belakang dan depan">
                @endif
            </td>
        </tr>
    </table>
    <table class="equip-wrap">
        <tr>
            <td>
                <table class="equip">
                    <thead>
                        <tr>
                            <th class="item">PERLENGKAPAN</th>
                            <th class="mark">ADA</th>
                            <th class="mark">TIDAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perlengkapan_kiri as $i => $item)
                            <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
                                <td class="item">{{ $item }}</td>
                                <td class="mark"></td>
                                <td class="mark"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td class="gap"></td>
            <td>
                <table class="equip">
                    <thead>
                        <tr>
                            <th class="item">PERLENGKAPAN</th>
                            <th class="mark">ADA</th>
                            <th class="mark">TIDAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perlengkapan_kanan as $i => $item)
                            <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
                                <td class="item">{{ $item }}</td>
                                <td class="mark"></td>
                                <td class="mark"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="heading">KONDISI KENDARAAN</div>
    <table class="cars-grid">
        <tr>
            <td>
                @if(!empty($cars['side_right']))
                    <img src="{{ $cars['side_right'] }}" alt="Samping kanan">
                @endif
                <div class="car-label">Samping Kanan</div>
            </td>
            <td>
                @if(!empty($cars['side_left']))
                    <img src="{{ $cars['side_left'] }}" alt="Samping kiri">
                @endif
                <div class="car-label">Samping Kiri</div>
            </td>
        </tr>
        <tr>
            <td>
                @if(!empty($cars['rear']))
                    <img src="{{ $cars['rear'] }}" alt="Belakang">
                @endif
                <div class="car-label">Belakang</div>
            </td>
            <td>
                @if(!empty($cars['front']))
                    <img src="{{ $cars['front'] }}" alt="Depan">
                @endif
                <div class="car-label">Depan</div>
            </td>
        </tr>
    </table>

    <div class="heading">KETERANGAN</div>
    <div class="notes">{{ $keterangan !== '' ? $keterangan : ' ' }}</div>

    <table class="sign">
        <tr>
            <td>
                <div class="sign-role">Pengirim</div>
                <div class="sign-space"></div>
                <div class="sign-line">Tanda Tangan &amp; Nama Terang</div>
            </td>
            <td>
                <div class="sign-role">Driver</div>
                <div class="sign-space"></div>
                <div class="sign-line">Tanda Tangan &amp; Nama Terang</div>
            </td>
            <td>
                <div class="sign-role">Penerima</div>
                <div class="sign-space"></div>
                <div class="sign-line">Tanda Tangan &amp; Nama Terang</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini sah tanpa tanda tangan basah / Dicetak otomatis dari sistem Royal {{ $brand_bottom }}
    </div>
</body>
</html>
