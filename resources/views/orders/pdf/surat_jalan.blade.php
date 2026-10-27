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
        @page { margin: 12mm 12mm 10mm 12mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #222;
            margin: 0;
        }
        table { width: 100%; border-collapse: collapse; }
        .top td { vertical-align: top; }
        .logo-img { width: 128px; height: auto; display: block; }
        .logo-box { font-family: Times-Roman, Times, serif; line-height: 0.95; }
        .logo-top { font-size: 20px; font-weight: bold; }
        .logo-bottom { font-size: 15px; font-weight: bold; }
        .center-head { text-align: left; padding: 0 12px; }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: {{ $brand_blue }};
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        .meta { font-size: 9px; }
        .meta td { padding: 1px 0; }
        .meta .lbl { width: 68px; }
        .meta .val { border-bottom: 1px dotted #999; padding-left: 4px; }
        .company { text-align: right; }
        .company-name {
            font-size: 11px;
            font-weight: bold;
            color: {{ $brand_blue }};
        }
        .company-line { color: #555; font-size: 8.5px; margin-top: 2px; }
        .heading {
            font-size: 11px;
            font-weight: bold;
            color: {{ $brand_blue }};
            letter-spacing: 0.4px;
            margin: 12px 0 6px;
        }
        .party { margin-top: 4px; }
        .party > tbody > tr > td { width: 50%; vertical-align: top; }
        .party .right { padding-left: 18px; }
        .line td { padding: 2px 0; vertical-align: bottom; }
        .line .lbl { width: 62px; color: #333; }
        .line .sep { width: 8px; }
        .line .val { border-bottom: 1px dotted #b3b3b3; padding: 0 3px 1px; }
        .vehicle > tbody > tr > td { vertical-align: top; }
        .vehicle .info { width: 58%; }
        .vehicle .diagram { width: 42%; text-align: center; padding-left: 8px; }
        .diagram img { display: block; margin: 2px auto; }
        .car-top { width: 168px; height: auto; }
        .car-side { width: 188px; height: auto; }
        .car-ends img { width: 78px; height: auto; display: inline-block; margin: 4px 10px 0; }
        .equip-wrap > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
        .equip-wrap .gap { width: 12px; }
        .equip { width: 100%; font-size: 8px; table-layout: fixed; }
        .equip th, .equip td {
            border: 1px solid #e0e0e0;
            padding: 3px 6px;
            vertical-align: middle;
        }
        .equip th {
            background: {{ $brand_blue }};
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .equip th.item, .equip td.item { text-align: left; width: 66%; }
        .equip .mark { width: 17%; text-align: center; }
        .equip tr.alt td { background: #f6f6f6; }
        .signs { margin-top: 16px; }
        .signs td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sign-box {
            height: 54px;
            background: #f2f2f2;
            border: 1px solid #ececec;
            margin-bottom: 6px;
        }
        .sign-label {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.6px;
        }
        .sign-name { font-size: 8px; color: #666; margin-top: 3px; }
        .bottom > tbody > tr > td { vertical-align: top; }
        .bottom .notice { width: 66%; padding-right: 16px; }
        .bottom .pay { width: 34%; }
        .notice-title, .pay-title {
            font-size: 10px;
            font-weight: bold;
            color: {{ $brand_blue }};
            margin: 12px 0 6px;
        }
        .notice ul { margin: 0; padding-left: 14px; }
        .notice li { margin-bottom: 3px; font-size: 7.5px; line-height: 1.35; color: #333; }
        .pay-body { font-size: 8px; line-height: 1.4; white-space: pre-wrap; }
    </style>
</head>
<body>
    <table class="top">
        <tr>
            <td style="width: 26%;">
                @if(!empty($logo_src))
                    <img src="{{ $logo_src }}" class="logo-img" alt="ROYAL TOWING">
                @else
                    <div class="logo-box">
                        <div class="logo-top">{{ $brand_top }}</div>
                        <div class="logo-bottom">{{ $brand_bottom }}</div>
                    </div>
                @endif
            </td>
            <td class="center-head" style="width: 42%;">
                <div class="doc-title">SURAT JALAN</div>
                <table class="meta">
                    <tr>
                        <td class="lbl">Nomor</td>
                        <td style="width: 8px;">:</td>
                        <td class="val">{{ $nomor }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Tanggal</td>
                        <td>:</td>
                        <td class="val">{{ $tanggal }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Kode Unit</td>
                        <td>:</td>
                        <td class="val">{{ $kode_unit }}</td>
                    </tr>
                </table>
            </td>
            <td class="company" style="width: 32%;">
                <div class="company-name">{{ $company_name }}</div>
                <div class="company-line">{{ $company_phone }}</div>
                <div class="company-line">{{ $company_email }}</div>
            </td>
        </tr>
    </table>

    <table class="party">
        <tr>
            <td>
                <div class="heading">PENGIRIM</div>
                <table class="line">
                    <tr>
                        <td class="lbl">Nama</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pengirim_nama }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No HP</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pengirim_telepon }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Alamat</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $pengirim_alamat }}</td>
                    </tr>
                </table>
            </td>
            <td class="right">
                <div class="heading">PENERIMA</div>
                <table class="line">
                    <tr>
                        <td class="lbl">Nama</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $penerima_nama }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No HP</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $penerima_telepon }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Alamat</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $penerima_alamat }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="vehicle">
        <tr>
            <td class="info">
                <div class="heading">DATA KENDARAAN</div>
                <table class="line">
                    <tr>
                        <td class="lbl">Merk / Tipe</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $merk_tipe }}</td>
                        <td style="width: 18px;"></td>
                        <td style="width: 42px;">Warna</td>
                        <td class="sep">:</td>
                        <td class="val">{{ $warna }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No Rangka</td>
                        <td class="sep">:</td>
                        <td class="val" colspan="5">{{ $no_rangka }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No Polisi</td>
                        <td class="sep">:</td>
                        <td class="val" colspan="5">{{ $nopol }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Catatan</td>
                        <td class="sep">:</td>
                        <td class="val" colspan="5">{{ $catatan !== '' ? $catatan : ' ' }}</td>
                    </tr>
                </table>
            </td>
            <td class="diagram">
                @if(!empty($cars['side_left']))
                    <img src="{{ $cars['side_left'] }}" class="car-top" alt="Samping">
                @endif
                @if(!empty($cars['side_right']))
                    <img src="{{ $cars['side_right'] }}" class="car-side" alt="Samping">
                @endif
                <div class="car-ends">
                    @if(!empty($cars['rear']))
                        <img src="{{ $cars['rear'] }}" alt="Belakang">
                    @endif
                    @if(!empty($cars['front']))
                        <img src="{{ $cars['front'] }}" alt="Depan">
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="heading">PERLENGKAPAN KENDARAAN</div>
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

    <table class="signs">
        <tr>
            <td>
                <div class="sign-box"></div>
                <div class="sign-label">PENGIRIM</div>
                <div class="sign-name">{{ $pengirim_nama }}</div>
            </td>
            <td>
                <div class="sign-box"></div>
                <div class="sign-label">PENERIMA</div>
                <div class="sign-name">{{ $penerima_nama }}</div>
            </td>
            <td>
                <div class="sign-box"></div>
                <div class="sign-label">DRIVER</div>
                <div class="sign-name">{{ $driver }}</div>
            </td>
        </tr>
    </table>

    <table class="bottom">
        <tr>
            <td class="notice">
                <div class="notice-title">PERHATIAN!</div>
                <ul>
                    <li>Setelah ditandatangani oleh pengirim, semua pihak secara SAH menyetujui isi dan ketentuan dari surat jalan ini.</li>
                    <li>Pengirim wajib memastikan tidak ada barang berharga di dalam kabin atau bagasi / tempat penyimpanan (motor / mobil); kehilangan menjadi tanggung jawab pengirim.</li>
                    <li>Segala kerusakan dan kerugian dalam pengiriman yang diakibatkan tidak adanya asuransi pengiriman, bukan menjadi tanggung jawab kami.</li>
                    <li>Klaim untuk hal-hal di luar asuransi akibat force majeure / pengangkutan MAKSIMAL sebesar nilai transaksi yang berlangsung (kwitansi).</li>
                </ul>
            </td>
            <td class="pay">
                <div class="pay-title">PEMBAYARAN</div>
                <div class="pay-body">{{ $pembayaran }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
