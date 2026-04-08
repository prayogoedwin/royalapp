<x-layouts.app>
    <div
        x-data="{
            query: '',
            loading: false,
            results: [],
            selected: @js($initialCrew ?? []),
            async search() {
                if (this.query.length < 1) {
                    this.results = [];
                    return;
                }
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('tasks.crew-search') }}?q=${encodeURIComponent(this.query)}`);
                    if (!response.ok) {
                        this.results = [];
                        return;
                    }
                    const data = await response.json();
                    this.results = (Array.isArray(data) ? data : []).filter(item => !this.selected.some(sel => Number(sel.id) === Number(item.id)));
                } catch (e) {
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },
            addCrew(item) {
                if (this.selected.some(sel => Number(sel.id) === Number(item.id))) return;
                this.selected.push(item);
                this.query = '';
                this.results = [];
            },
            removeCrew(id) {
                this.selected = this.selected.filter(item => Number(item.id) !== Number(id));
            }
        }"
    >
        <div class="mb-6 flex items-center text-sm">
            <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('tasks.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Tasks') }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-500 dark:text-gray-400">{{ __('Create') }}</span>
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Create Task</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Create internal task with crew and status.</p>
        </div>

        <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            @csrf
            <div>
                <label class="block text-sm mb-1">Title</label>
                <input name="title" value="{{ old('title') }}" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm mb-1">Description</label>
                <textarea name="description" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select name="order_status_id" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                    <option value="">-</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @selected(old('order_status_id') == $status->id)>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative">
                <label class="block text-sm mb-1">Crew</label>
                <input
                    type="text"
                    x-model="query"
                    @input.debounce.300ms="search()"
                    placeholder="Cari nama, NIK, HP, atau email user…"
                    class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700"
                >
                <div x-show="loading" class="mt-2 text-xs text-gray-500">Searching...</div>
                <div x-show="results.length > 0" class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-56 overflow-auto">
                    <template x-for="item in results" :key="item.id">
                        <button
                            type="button"
                            @click="addCrew(item)"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                            x-text="item.name"
                        ></button>
                    </template>
                </div>

                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="item in selected" :key="item.id">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <span x-text="item.name"></span>
                            <button type="button" @click="removeCrew(item.id)" class="text-blue-700 dark:text-blue-200">x</button>
                            <input type="hidden" name="crew_ids[]" :value="item.id">
                        </span>
                    </template>
                </div>
            </div>

            <div class="flex gap-2">
                <x-button type="primary">Save</x-button>
                <a href="{{ route('tasks.index') }}"><x-button type="secondary">Back</x-button></a>
            </div>
        </form>
    </div>
</x-layouts.app>

