<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $brand_blue = $brand_blue ?? '#4CA7DC';
        $cars = $cars ?? [];
        $editable = $editable ?? false;
        $is_public = $is_public ?? false;
        $saved = $saved ?? false;
        $save_url = $save_url ?? null;
        $save_token = $save_token ?? null;
        $csrf = $csrf ?? null;
        $public_url = $public_url ?? null;
        $wa_url = $wa_url ?? null;
        $wa_native_url = $wa_native_url ?? null;
        $wa_intent_url = $wa_intent_url ?? null;
        $is_webview = $is_webview ?? false;
        $checklist_map = $checklist_map ?? [];
        $ttd_urls = $ttd_urls ?? ['pengirim' => null, 'penerima' => null, 'driver' => null];
        $show_toolbar = $editable || ($public_url && ! $is_public);
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
        .mark-choice { display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 18px; cursor: pointer; }
        .mark-choice input { position: absolute; opacity: 0; width: 0; height: 0; }
        .mark-choice .tick { color: transparent; font-weight: bold; }
        .mark-choice input:checked + .tick { color: #111; }
        .sign-box { position: relative; overflow: hidden; }
        .sign-box img, .sign-box canvas { width: 100%; height: 54px; display: block; }
        .sign-box canvas { touch-action: none; background: #f2f2f2; cursor: crosshair; }
        .sj-toolbar {
            display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 14px;
            position: sticky; top: 0; background: #fff; padding: 8px 0; z-index: 5;
        }
        .sj-toolbar button, .sj-toolbar a {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 12px; border-radius: 8px; border: 0; font-size: 13px;
            font-weight: 600; text-decoration: none; cursor: pointer; color: #fff;
        }
        .sj-btn-save { background: #2563eb; }
        .sj-btn-print { background: #374151; }
        .sj-btn-wa { background: #16a34a; }
        .sj-btn-download { background: #0f766e; }
        .sj-status { font-size: 12px; color: #166534; margin: 0 0 10px; }
        .sj-status.error { color: #b91c1c; }
        @media screen {
            body { max-width: 800px; margin: 0 auto; padding: 16px; background: #fff; }
        }
        @media print {
            .sj-toolbar, .sj-status, .sj-resign { display: none !important; }
            body { max-width: none; padding: 0; }
            .sign-box canvas { display: none !important; }
        }
    </style>
</head>
<body>
    @if($show_toolbar)
        <div class="sj-toolbar">
            @if($editable && $save_url)
                <button type="button" class="sj-btn-save" id="sj-save">Simpan</button>
            @endif
            @if(! $is_webview)
                <button type="button" class="sj-btn-print" id="sj-print">Cetak</button>
            @endif
            @if($wa_url)
                <button type="button" class="sj-btn-wa" id="sj-wa"
                    data-wa="{{ $wa_url }}"
                    data-native="{{ $wa_native_url }}"
                    data-intent="{{ $wa_intent_url }}"
                    data-copy="{{ $public_url }}">Kirim WA</button>
            @elseif($editable)
                <button type="button" class="sj-btn-wa" id="sj-wa"
                    data-wa="" data-native="" data-intent="" data-copy="" hidden>Kirim WA</button>
            @endif
            @if($public_url && ! $is_webview)
                <button type="button" class="sj-btn-download" id="sj-copy" data-copy="{{ $public_url }}">Salin link</button>
                <a class="sj-btn-download" href="{{ $public_url }}?download=1">Unduh HTML</a>
            @endif
        </div>
        <div class="sj-status" id="sj-status" @if(!$saved) hidden @endif>Checklist surat jalan tersimpan.</div>
    @endif
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
                            @include('orders.pdf.partials.perlengkapan-row', ['item' => $item, 'i' => $i])
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
                            @include('orders.pdf.partials.perlengkapan-row', ['item' => $item, 'i' => $i])
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table class="signs">
        <tr>
            @foreach(['pengirim' => $pengirim_nama, 'penerima' => $penerima_nama, 'driver' => $driver] as $role => $signName)
                <td>
                    <div class="sign-box" data-role="{{ $role }}">
                        @if(!empty($ttd_urls[$role]))
                            <img src="{{ $ttd_urls[$role] }}" alt="TTD {{ $role }}" class="sj-ttd-img">
                        @endif
                        @if($editable)
                            <canvas class="sj-pad" data-role="{{ $role }}" @if(!empty($ttd_urls[$role])) hidden @endif></canvas>
                        @endif
                    </div>
                    @if($editable)
                        <button type="button" class="sj-resign" data-role="{{ $role }}" style="margin:4px 0;font-size:9px;background:none;border:0;color:#2563eb;cursor:pointer;">
                            {{ !empty($ttd_urls[$role]) ? 'Tanda tangan ulang' : 'Tanda tangan di sini' }}
                        </button>
                    @endif
                    <div class="sign-label">{{ strtoupper($role) }}</div>
                    <div class="sign-name">{{ $signName }}</div>
                </td>
            @endforeach
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
    @if($editable && $save_url)
        <script>
            window.sjConfig = {
                saveUrl: @json($save_url),
                token: @json($save_token),
                csrf: @json($csrf)
            };

            (function () {
                var pads = {};
                var dirty = {};

                function setupPad(canvas) {
                    var role = canvas.getAttribute('data-role');
                    var ctx = canvas.getContext('2d');
                    var drawing = false;
                    function size() {
                        var rect = canvas.getBoundingClientRect();
                        var ratio = window.devicePixelRatio || 1;
                        canvas.width = Math.max(rect.width, 120) * ratio;
                        canvas.height = 54 * ratio;
                        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                        ctx.strokeStyle = '#111';
                        ctx.lineWidth = 2;
                        ctx.lineCap = 'round';
                    }
                    size();
                    function pos(event) {
                        var rect = canvas.getBoundingClientRect();
                        var point = event.touches ? event.touches[0] : event;
                        return { x: point.clientX - rect.left, y: point.clientY - rect.top };
                    }
                    function start(event) {
                        event.preventDefault();
                        drawing = true;
                        dirty[role] = true;
                        var p = pos(event);
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                    }
                    function move(event) {
                        if (!drawing) return;
                        event.preventDefault();
                        var p = pos(event);
                        ctx.lineTo(p.x, p.y);
                        ctx.stroke();
                    }
                    function end() { drawing = false; }
                    canvas.addEventListener('mousedown', start);
                    canvas.addEventListener('mousemove', move);
                    window.addEventListener('mouseup', end);
                    canvas.addEventListener('touchstart', start, { passive: false });
                    canvas.addEventListener('touchmove', move, { passive: false });
                    canvas.addEventListener('touchend', end);
                    pads[role] = canvas;
                }

                document.querySelectorAll('canvas.sj-pad').forEach(setupPad);

                document.querySelectorAll('.sj-resign').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var role = button.getAttribute('data-role');
                        var box = button.previousElementSibling;
                        var img = box.querySelector('img');
                        var canvas = box.querySelector('canvas');
                        if (img) img.hidden = true;
                        if (canvas) {
                            canvas.hidden = false;
                            var ctx = canvas.getContext('2d');
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            dirty[role] = false;
                        }
                    });
                });

                var printBtn = document.getElementById('sj-print');
                if (printBtn) {
                    printBtn.addEventListener('click', function () {
                        window.print();
                    });
                }

                document.getElementById('sj-save').addEventListener('click', function () {
                    var items = [];
                    var seen = {};
                    document.querySelectorAll('input[data-item]').forEach(function (input) {
                        var name = input.getAttribute('data-item');
                        if (!seen[name]) {
                            seen[name] = true;
                            items.push({ name: name, status: '' });
                        }
                        if (input.checked) {
                            items.filter(function (row) { return row.name === name; })[0].status = input.value;
                        }
                    });
                    var signatures = {};
                    Object.keys(pads).forEach(function (role) {
                        signatures[role] = dirty[role] ? pads[role].toDataURL('image/png') : '';
                    });
                    var status = document.getElementById('sj-status');
                    status.hidden = false;
                    status.classList.remove('error');
                    status.textContent = 'Menyimpan...';
                    var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
                    if (window.sjConfig.csrf) headers['X-CSRF-TOKEN'] = window.sjConfig.csrf;
                    if (window.sjConfig.token) headers['Authorization'] = 'Bearer ' + window.sjConfig.token;
                    fetch(window.sjConfig.saveUrl, {
                        method: 'POST',
                        headers: headers,
                        credentials: 'same-origin',
                        body: JSON.stringify({ items: items, signatures: signatures })
                    }).then(function (res) { return res.json().then(function (body) { return { ok: res.ok, body: body }; }); })
                    .then(function (result) {
                        if (!result.ok) {
                            throw new Error(result.body.message || 'Gagal menyimpan');
                        }
                        status.textContent = 'Tersimpan. Silakan cetak atau kirim WA.';
                        if (result.body.data && result.body.data.wa_url) {
                            var wa = document.getElementById('sj-wa');
                            wa.setAttribute('data-wa', result.body.data.wa_url);
                            wa.setAttribute('data-native', result.body.data.wa_native_url || '');
                            wa.setAttribute('data-intent', result.body.data.wa_intent_url || '');
                            wa.setAttribute('data-copy', result.body.data.public_url || '');
                            wa.hidden = false;
                        }
                        if (result.body.data && result.body.data.public_url) {
                            window.location.reload();
                        }
                    }).catch(function (error) {
                        status.classList.add('error');
                        status.textContent = error.message || 'Gagal menyimpan';
                    });
                });
            })();
        </script>
    @elseif($public_url || $editable)
        <script>
            document.getElementById('sj-print') && document.getElementById('sj-print').addEventListener('click', function () {
                window.print();
            });
        </script>
    @endif
    <script>
        function copyText(text) {
            if (!text) {
                return false;
            }
            var area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.left = '-9999px';
            document.body.appendChild(area);
            area.select();
            var ok = false;
            try { ok = document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(area);
            if (!ok && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
                ok = true;
            }
            return ok;
        }

        function isAppWebView() {
            var ua = navigator.userAgent || '';
            return @json($is_webview)
                || /\bwv\b|; wv\)|WebView/i.test(ua)
                || typeof window.ReactNativeWebView !== 'undefined'
                || typeof window.flutter_inappwebview !== 'undefined'
                || typeof window.Android !== 'undefined';
        }

        function showStatus(message, isError) {
            var status = document.getElementById('sj-status');
            if (!status) {
                return;
            }
            status.hidden = false;
            status.classList.toggle('error', !!isError);
            status.textContent = message;
        }

        function openExternal(url) {
            if (!url) {
                return false;
            }
            if (window.ReactNativeWebView) {
                window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'open_url', url: url }));
                return true;
            }
            if (window.flutter_inappwebview && window.flutter_inappwebview.callHandler) {
                window.flutter_inappwebview.callHandler('openUrl', url);
                return true;
            }
            if (window.Android && typeof window.Android.openUrl === 'function') {
                window.Android.openUrl(url);
                return true;
            }
            if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.openUrl) {
                window.webkit.messageHandlers.openUrl.postMessage(url);
                return true;
            }
            return false;
        }

        function openWhatsAppApp(nativeUrl, intentUrl) {
            var target = intentUrl || nativeUrl;
            if (!target) {
                return false;
            }
            try {
                window.location.href = target;
                return true;
            } catch (e) {
                return false;
            }
        }

        function bindWhatsAppButton() {
            var wa = document.getElementById('sj-wa');
            if (wa && wa.getAttribute('data-bound') !== '1') {
                wa.setAttribute('data-bound', '1');
                wa.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var href = wa.getAttribute('data-wa') || '';
                    var native = wa.getAttribute('data-native') || '';
                    var intent = wa.getAttribute('data-intent') || '';
                    var share = wa.getAttribute('data-copy') || '';
                    if (!href && !native && !intent) {
                        showStatus('Nomor HP owner belum valid untuk WhatsApp.', true);
                        return;
                    }
                    if (openExternal(native || intent || href)) {
                        showStatus('Membuka WhatsApp ke nomor owner...');
                        return;
                    }
                    if (isAppWebView()) {
                        if (openWhatsAppApp(native, intent)) {
                            showStatus('Membuka WhatsApp ke nomor owner...');
                            return;
                        }
                        showStatus('Tidak bisa membuka WhatsApp dari aplikasi. Pastikan WhatsApp terpasang.', true);
                        return;
                    }
                    var popup = window.open(href, '_blank');
                    if (popup) {
                        showStatus('WhatsApp dibuka ke nomor owner.');
                        return;
                    }
                    window.location.href = href;
                });
            }

            var copyBtn = document.getElementById('sj-copy');
            if (copyBtn && copyBtn.getAttribute('data-bound') !== '1') {
                copyBtn.setAttribute('data-bound', '1');
                copyBtn.addEventListener('click', function () {
                    if (copyText(copyBtn.getAttribute('data-copy') || '')) {
                        showStatus('Link disalin. Tempel di WhatsApp.');
                    }
                });
            }
        }
        bindWhatsAppButton();
    </script>
</body>
</html>
