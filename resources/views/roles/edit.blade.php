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
        <span class="text-gray-500 dark:text-gray-400">{{ __('Edit') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Edit Role') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update role details and permissions') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('roles.update', $role) }}" method="POST" class="max-w-2xl">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <x-forms.input label="Name" name="name" type="text" value="{{ old('name', $role->name) }}" required />
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Permissions') }}
                        </label>
                        <button type="button" onclick="toggleAllPermissions()" 
                            class="text-xs px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800">
                            {{ __('Select All / Deselect All') }}
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($groupedPermissions as $resource => $group)
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-md border border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $group['name'] }}
                                    </h3>
                                    <button type="button" 
                                        onclick="toggleGroupPermissions('{{ $resource }}')" 
                                        class="text-xs px-2 py-1 bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                                        {{ __('Select All') }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($group['permissions'] as $permission)
                                        <div>
                                            <x-forms.checkbox 
                                                name="permissions[]" 
                                                value="{{ $permission['id'] }}" 
                                                label="{{ $permission['label'] }}"
                                                data-group="{{ $resource }}"
                                                :checked="in_array($permission['id'], old('permissions', $role->permissions->pluck('id')->toArray()))" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No permissions available.') }}</p>
                        @endforelse
                    </div>
                    
                    @error('permissions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <script>
                    function toggleAllPermissions() {
                        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        checkboxes.forEach(cb => cb.checked = !allChecked);
                    }

                    function toggleGroupPermissions(group) {
                        const checkboxes = document.querySelectorAll(`input[data-group="${group}"]`);
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        checkboxes.forEach(cb => cb.checked = !allChecked);
                    }
                </script>

                <div class="flex gap-3">
                    <x-button type="primary">{{ __('Update') }}</x-button>
                    <a href="{{ route('roles.index') }}">
                        <x-button type="secondary">{{ __('Cancel') }}</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
