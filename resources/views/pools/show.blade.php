<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('pools.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Pools') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('View') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $pool->pool_name }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Pool detail') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-pools'))
                <a href="{{ route('pools.edit', $pool) }}">
                    <x-button type="primary">{{ __('Edit Pool') }}</x-button>
                </a>
            @endif
            <a href="{{ route('pools.index') }}">
                <x-button type="secondary">{{ __('Back') }}</x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Information') }}</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Pool Name') }}</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $pool->pool_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Lat') }}</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $pool->lat ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Lng') }}</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $pool->lng ?? '-' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Address') }}</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $pool->address ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Audit') }}</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Created At</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $pool->created_at?->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Created By</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $pool->createdBy->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Updated At</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $pool->updated_at?->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Updated By</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $pool->updatedBy->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Deleted At</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $pool->deleted_at?->format('d M Y H:i') ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

