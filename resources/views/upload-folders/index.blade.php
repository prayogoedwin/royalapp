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
                                <div class="flex flex-wrap items-center gap-2" x-data="{ confirming: {{ session('confirm_folder') === $folder['year'].'/'.$folder['month'] ? 'true' : 'false' }} }">
                                    <a href="{{ route('upload-folders.download', ['year' => $folder['year'], 'month' => $folder['month']]) }}"
                                       class="px-3 py-2 bg-gray-700 text-white rounded-md text-xs hover:bg-gray-800">
                                        {{ __('Download') }}
                                    </a>
                                    @if(auth()->user()->hasPermission('delete-upload-folders'))
                                        <button type="button" x-show="!confirming" @click="confirming = true"
                                            class="px-3 py-2 bg-red-600 text-white rounded-md text-xs hover:bg-red-700">
                                            {{ __('Hapus Folder Bulan') }}
                                        </button>
                                        <form method="POST" x-show="confirming" x-cloak
                                            action="{{ route('upload-folders.destroy', ['year' => $folder['year'], 'month' => $folder['month']]) }}"
                                            class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('DELETE')
                                            <span class="text-xs text-gray-600 dark:text-gray-300">
                                                Ketik
                                                <span class="font-mono font-bold tracking-widest text-gray-900 dark:text-gray-100">{{ $folder['confirmation_code'] }}</span>
                                            </span>
                                            <input type="text" name="confirmation_code" maxlength="4" required autocomplete="off" spellcheck="false"
                                                class="w-20 uppercase tracking-widest text-center rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm"
                                                placeholder="____">
                                            <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md text-xs hover:bg-red-700">
                                                {{ __('Hapus') }}
                                            </button>
                                            <button type="button" @click="confirming = false" class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300 hover:underline">
                                                {{ __('Batal') }}
                                            </button>
                                            @if(session('confirm_folder') === $folder['year'].'/'.$folder['month'] && $errors->has('confirmation_code'))
                                                <p class="w-full text-xs text-red-600 dark:text-red-400">{{ $errors->first('confirmation_code') }}</p>
                                            @endif
                                        </form>
                                    @endif
                                </div>
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

