<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('permissions.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Permissions') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('View') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('View Permission') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Permission details and assigned roles') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-permissions'))
                <a href="{{ route('permissions.edit', $permission) }}">
                    <x-button type="primary">{{ __('Edit Permission') }}</x-button>
                </a>
            @endif
            <a href="{{ route('permissions.index') }}">
                <x-button type="secondary">{{ __('Back') }}</x-button>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="max-w-2xl">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Permission Name') }}
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $permission->name }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Created At') }}
                    </label>
                    <div class="text-gray-900 dark:text-gray-100">
                        {{ $permission->created_at->format('M d, Y H:i') }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {{ __('Assigned to Roles') }} ({{ $permission->roles->count() }})
                    </label>
                    @if($permission->roles->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($permission->roles as $role)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Not assigned to any role.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
