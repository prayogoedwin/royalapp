<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan {{ $nomor }}</title>
    <style>
        @page { margin: 18mm 16mm 16mm 16mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
        }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .logo-img {
            width: 148px;
            height: auto;
            display: block;
        }
        .logo-box {
            width: 118px;
            text-align: left;
            line-height: 0.95;
            font-family: Times-Roman, Times, serif;
        }
        .logo-top {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #111;
        }
        .logo-bottom {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #111;
        }
        .company { text-align: right; }
        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.3px;
        }
        .company-line { color: #333; font-size: 10px; margin-top: 2px; }
        .rule {
            border: 0;
            border-top: 2px solid #1e3a8a;
            margin: 10px 0 8px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 4px 0 10px;
        }
        .meta td { padding: 1px 0; }
        .meta .label { width: 58px; }
        .section {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.6px;
            background: #1e3a8a;
            color: #fff;
            padding: 5px 8px;
            margin: 10px 0 0;
        }
        .box { border: 1px solid #1e3a8a; border-top: 0; }
        .kv { width: 100%; }
        .kv td { padding: 6px 8px; vertical-align: top; }
        .kv .lbl { width: 28%; color: #333; }
        .kv .sep { width: 10px; }
        .split td { width: 50%; vertical-align: top; }
        .split .left { border-right: 1px solid #1e3a8a; }
        .equip-wrap td { width: 50%; vertical-align: top; padding: 0; }
        .equip-wrap .gap { width: 8px; }
        .equip {
            width: 100%;
            font-size: 8.5px;
        }
        .equip th, .equip td {
            border: 1px solid #c5d4e8;
            padding: 3px 5px;
            vertical-align: middle;
        }
        .equip th {
            background: #3b82f6;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .equip th.item { text-align: left; }
        .equip td.item { width: auto; }
        .equip .mark { width: 36px; text-align: center; }
        .equip tr.alt td { background: #f3f6fa; }
        .notes {
            min-height: 36px;
            padding: 6px 10px;
            white-space: pre-wrap;
        }
        .sign { margin-top: 12px; }
        .sign td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
        }
        .sign-role { font-weight: bold; }
        .sign-space { height: 46px; }
        .sign-line {
            border-top: 1px solid #111;
            margin: 0 18px;
            padding-top: 4px;
            font-size: 9px;
            color: #444;
        }
        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 160px;">
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

    <div class="section">DATA KENDARAAN</div>
    <div class="box">
        <table class="kv">
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
    </div>

    <table class="split" style="margin-top: 10px;">
        <tr>
            <td class="left">
                <div class="section" style="margin-top: 0;">PENGIRIM</div>
                <div class="box">
                    <table class="kv">
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
                </div>
            </td>
            <td>
                <div class="section" style="margin-top: 0;">PENERIMA</div>
                <div class="box">
                    <table class="kv">
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
                </div>
            </td>
        </tr>
    </table>

    <div class="section">PERLENGKAPAN KENDARAAN</div>
    <div class="box" style="padding: 6px;">
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
    </div>

    <div class="section">KETERANGAN</div>
    <div class="box notes">{{ $keterangan !== '' ? $keterangan : ' ' }}</div>

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
