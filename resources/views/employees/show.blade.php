<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('employees.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Employees') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('View') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('View Employee') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Employee details') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-employees'))
                <a href="{{ route('employees.edit', $employee) }}">
                    <x-button type="primary">{{ __('Edit Employee') }}</x-button>
                </a>
            @endif
            @php
                $myEmployeeId = auth()->user()?->employee?->id;
                $presensiHref = ($myEmployeeId && (int)$myEmployeeId === (int)$employee->id)
                    ? route('presensi.my')
                    : route('employees.presensi', $employee);
            @endphp
            @if($myEmployeeId && (int)$myEmployeeId === (int)$employee->id)
                @if(auth()->user()->hasPermission('view-presensi'))
                    <a href="{{ $presensiHref }}">
                        <x-button type="secondary">{{ __('Lihat Presensi') }}</x-button>
                    </a>
                @endif
            @else
                @if(auth()->user()->hasPermission('view-presensi-all'))
                    <a href="{{ $presensiHref }}">
                        <x-button type="secondary">{{ __('Lihat Presensi') }}</x-button>
                    </a>
                @endif
            @endif
            <a href="{{ route('employees.index') }}">
                <x-button type="secondary">{{ __('Back') }}</x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Account Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('User Account Information') }}</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('User Name') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->user->name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Email') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->user->email ?? '-' }}
                            </div>
                        </div>

                        @if($employee->user && $employee->user->roles->count() > 0)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Roles') }}
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($employee->user->roles as $role)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Employee Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Employee Information') }}</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('NIK') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100 font-semibold">
                                {{ $employee->nik }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Full Name') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->full_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Position') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->position->nama }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Division') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->division->nama }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Pool') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->pool->pool_name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Employee Type') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->employeeType->nama }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Status') }}
                            </label>
                            <div>
                                @php
                                    $colors = [
                                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'inactive' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'resigned' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    ];
                                    $color = $colors[$employee->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $color }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Personal Information') }}</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Phone') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->phone ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Birth Date') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->birth_date ? $employee->birth_date->format('M d, Y') : '-' }}
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Address') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->address ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Employment Dates -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Employment Dates') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Join Date') }}
                        </label>
                        <div class="text-gray-900 dark:text-gray-100">
                            {{ $employee->join_date->format('M d, Y') }}
                        </div>
                    </div>

                    @if($employee->resign_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Resign Date') }}
                        </label>
                        <div class="text-gray-900 dark:text-gray-100">
                            {{ $employee->resign_date->format('M d, Y') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Record Information') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Created At') }}
                        </label>
                        <div class="text-gray-900 dark:text-gray-100 text-sm">
                            {{ $employee->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Updated At') }}
                        </label>
                        <div class="text-gray-900 dark:text-gray-100 text-sm">
                            {{ $employee->updated_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
