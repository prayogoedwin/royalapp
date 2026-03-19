<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Upload Folders') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Upload Folders') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Kelola folder upload berdasarkan tahun dan bulan.') }}
        </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-4 text-left">Tahun</th>
                        <th class="py-2 pr-4 text-left">Bulan</th>
                        <th class="py-2 pr-4 text-left">Folder</th>
                        <th class="py-2 pr-4 text-left">Total File</th>
                        <th class="py-2 pr-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($folders as $folder)
                        <tr>
                            <td class="py-2 pr-4">{{ $folder['year'] }}</td>
                            <td class="py-2 pr-4">{{ $folder['month'] }}</td>
                            <td class="py-2 pr-4 font-mono text-xs">{{ $folder['path'] }}</td>
                            <td class="py-2 pr-4">{{ $folder['total_files'] }}</td>
                            <td class="py-2 pr-4">
                                @if(auth()->user()->hasPermission('delete-upload-folders'))
                                    <form method="POST" action="{{ route('upload-folders.destroy', ['year' => $folder['year'], 'month' => $folder['month']]) }}"
                                          onsubmit="return confirm('Hapus folder {{ $folder['year'] }}/{{ $folder['month'] }} beserta data terkait?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md text-xs hover:bg-red-700">
                                            {{ __('Hapus Folder Bulan') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                {{ __('Belum ada folder upload berbasis tahun/bulan.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

