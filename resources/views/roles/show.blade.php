<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('roles.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Roles') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('View') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('View Role') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Role details and permissions') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-roles'))
                <a href="{{ route('roles.edit', $role) }}">
                    <x-button type="primary">{{ __('Edit Role') }}</x-button>
                </a>
            @endif
            <a href="{{ route('roles.index') }}">
                <x-button type="secondary">{{ __('Back') }}</x-button>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="max-w-2xl">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Role Name') }}
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $role->name }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Created At') }}
                    </label>
                    <div class="text-gray-900 dark:text-gray-100">
                        {{ $role->created_at->format('M d, Y H:i') }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {{ __('Permissions') }} ({{ $role->permissions->count() }})
                    </label>
                    @if($role->permissions->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($role->permissions as $permission)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $permission->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No permissions assigned.') }}</p>
                    @endif
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {{ __('Users with this Role') }} ({{ $role->users->count() }})
                    </label>
                    @if($role->users->count() > 0)
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-700">
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($role->users as $user)
                                    <div class="px-4 py-3 flex items-center justify-between">
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No users assigned to this role.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
