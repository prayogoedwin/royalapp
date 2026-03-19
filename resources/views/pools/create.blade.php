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
        <span class="text-gray-500 dark:text-gray-400">{{ __('Create') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Create Pool') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Add new pool location') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden max-w-3xl">
        <form action="{{ route('pools.store') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-6">
                <x-forms.input label="Pool Name" name="pool_name" type="text" value="{{ old('pool_name') }}" required />

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Address
                    </label>
                    <textarea name="address" rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('address') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-forms.input label="Latitude (lat)" name="lat" type="number" step="0.00000001"
                            value="{{ old('lat') }}" />
                    </div>
                    <div>
                        <x-forms.input label="Longitude (lng)" name="lng" type="number" step="0.00000001"
                            value="{{ old('lng') }}" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('pools.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Cancel') }}
                </a>
                <x-button type="primary">{{ __('Create Pool') }}</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>

