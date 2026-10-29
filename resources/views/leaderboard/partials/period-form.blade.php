@php
    $bulanLabels = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

<form
    method="GET"
    action="{{ $action }}"
    x-ref="form"
    class="mb-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4"
    x-data="{
        allTime: {{ $allTime ? 'true' : 'false' }},
        startDate: {{ \Illuminate\Support\Js::from($startDate) }},
        endDate: {{ \Illuminate\Support\Js::from($endDate) }},
        year: {{ (int) $year }},
        month: {{ \Illuminate\Support\Js::from((string) ($month ?? '')) }},
        pad(n) { return String(n).padStart(2, '0') },
        ymd(date) {
            return date.getFullYear() + '-' + this.pad(date.getMonth() + 1) + '-' + this.pad(date.getDate())
        },
        submitForm() {
            this.$nextTick(() => this.$refs.form.submit())
        },
        applyToday() {
            const today = this.ymd(new Date())
            this.allTime = false
            this.startDate = today
            this.endDate = today
            this.year = new Date().getFullYear()
            this.month = ''
            this.submitForm()
        },
        applyYear() {
            this.startDate = this.year + '-01-01'
            this.endDate = this.year + '-12-31'
            this.month = ''
        },
        applyMonth() {
            if (!this.month) {
                this.applyYear()
                return
            }
            const month = Number(this.month)
            const last = new Date(this.year, month, 0)
            this.startDate = this.year + '-' + this.pad(month) + '-01'
            this.endDate = this.ymd(last)
        },
        applyThisMonth() {
            const now = new Date()
            this.allTime = false
            this.year = now.getFullYear()
            this.month = String(now.getMonth() + 1)
            this.applyMonth()
            this.submitForm()
        },
        applyThisYear() {
            this.allTime = false
            this.year = new Date().getFullYear()
            this.applyYear()
            this.submitForm()
        }
    }"
>
    @if(($driverScope ?? 'ongoing') === 'all')
        <input type="hidden" name="drivers" value="all">
    @endif
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-4">
        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
            <input
                type="checkbox"
                name="all_time"
                value="1"
                class="rounded border-gray-300 dark:border-gray-600"
                x-model="allTime"
                @change="submitForm()"
                @if($allTime) checked @endif
            >
            <span>{{ __('Sepanjang waktu (tanpa filter tanggal)') }}</span>
        </label>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md w-fit">
            {{ __('Terapkan') }}
        </button>
    </div>

    <div class="space-y-4" x-show="!allTime" x-cloak>
        <div class="flex flex-col sm:flex-row flex-wrap items-end gap-3">
            <div>
                <label for="lb_start_date" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Tanggal mulai') }}</label>
                <input
                    id="lb_start_date"
                    type="date"
                    name="start_date"
                    value="{{ $startDate }}"
                    x-model="startDate"
                    class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                >
            </div>
            <div>
                <label for="lb_end_date" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Tanggal selesai') }}</label>
                <input
                    id="lb_end_date"
                    type="date"
                    name="end_date"
                    value="{{ $endDate }}"
                    x-model="endDate"
                    class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
                >
            </div>
            <div class="flex flex-wrap gap-2 pb-0.5">
                <button type="button" class="px-3 py-2 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" @click="applyToday()">
                    {{ __('Hari ini') }}
                </button>
                <button type="button" class="px-3 py-2 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" @click="applyThisMonth()">
                    {{ __('Bulan ini') }}
                </button>
                <button type="button" class="px-3 py-2 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" @click="applyThisYear()">
                    {{ __('Tahun ini') }}
                </button>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row flex-wrap items-end gap-3">
            <div>
                <label for="lb_year" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Tahun') }}</label>
                <select
                    id="lb_year"
                    name="year"
                    x-model.number="year"
                    @change="applyMonth(); submitForm()"
                    class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm min-w-[7rem]"
                >
                    @for($y = $yearOptionsTo; $y >= $yearOptionsFrom; $y--)
                        <option value="{{ $y }}" @selected((int) $year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="lb_month" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Bulan') }}</label>
                <select
                    id="lb_month"
                    name="month"
                    x-model="month"
                    @change="applyMonth(); submitForm()"
                    class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm min-w-[11rem]"
                >
                    <option value="">{{ __('Semua bulan (tahun penuh)') }}</option>
                    @foreach($bulanLabels as $num => $label)
                        <option value="{{ $num }}" @selected((string) $month === (string) $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
        {{ __('Default: hari ini. Klik Hari ini / Bulan ini / Tahun ini untuk memuat data, atau isi tanggal lalu Terapkan.') }}
        <br>
        <span class="opacity-90">{{ $periodHint ?? __('Order: tanggal jemput (pickup), atau tanggal dibuat jika jemput kosong. Task: tanggal dibuat. Absensi: tanggal. Komentar & lampiran: waktu buat.') }}</span>
    </p>
</form>
