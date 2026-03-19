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
        <span class="text-gray-500 dark:text-gray-400">{{ __('Create') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Create Employee') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create a new employee with user account') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('employees.store') }}" method="POST" class="max-w-4xl">
                @csrf

                <!-- User Account Section -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('User Account Information') }}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="Name" name="name" type="text" value="{{ old('name') }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Email" name="email" type="email" value="{{ old('email') }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Password" name="password" type="password" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Confirm Password" name="password_confirmation" type="password" required />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Roles') }}
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($roles as $role)
                                    <div>
                                        <x-forms.checkbox 
                                            name="roles[]" 
                                            value="{{ $role->id }}" 
                                            label="{{ $role->name }}"
                                            :checked="in_array($role->id, old('roles', []))" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Information Section -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Employee Information') }}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="NIK" name="nik" type="text" value="{{ old('nik') }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Full Name" name="full_name" type="text" value="{{ old('full_name') }}" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Position') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="position_id" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">{{ __('Select Position') }}</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                        {{ $position->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Division') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="division_id" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">{{ __('Select Division') }}</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Pool') }}
                            </label>
                            <select name="pool_id"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">{{ __('No Pool') }}</option>
                                @foreach($pools as $pool)
                                    <option value="{{ $pool->id }}" {{ old('pool_id') == $pool->id ? 'selected' : '' }}>
                                        {{ $pool->pool_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pool_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Employee Type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="employee_type_id" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">{{ __('Select Type') }}</option>
                                @foreach($employeeTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('employee_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_type_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Status') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="resigned" {{ old('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Personal Information') }}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="Phone" name="phone" type="text" value="{{ old('phone') }}" />
                        </div>
                        
                        <div>
                            <x-forms.input label="Birth Date" name="birth_date" type="date" value="{{ old('birth_date') }}" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Address') }}
                            </label>
                            <textarea name="address" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Employment Dates Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Employment Dates') }}</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="Join Date" name="join_date" type="date" value="{{ old('join_date', date('Y-m-d')) }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Resign Date" name="resign_date" type="date" value="{{ old('resign_date') }}" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <x-button type="primary">{{ __('Create Employee') }}</x-button>
                    <a href="{{ route('employees.index') }}">
                        <x-button type="secondary">{{ __('Cancel') }}</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
