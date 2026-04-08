<x-layouts.app>
    <div
        x-data="{
            showAttachmentModal: false,
            showCommentModal: false,
            attachmentType: 'photo',
            commentType: '',
            crewQuery: '',
            crewLoading: false,
            crewResults: [],
            selectedCrew: @js($initialCrew ?? []),
            async searchCrew() {
                if (this.crewQuery.length < 1) {
                    this.crewResults = [];
                    return;
                }
                this.crewLoading = true;
                try {
                    const response = await fetch(`{{ route('tasks.crew-search') }}?q=${encodeURIComponent(this.crewQuery)}`);
                    if (!response.ok) {
                        this.crewResults = [];
                        return;
                    }
                    const data = await response.json();
                    this.crewResults = (Array.isArray(data) ? data : []).filter(item => !this.selectedCrew.some(sel => Number(sel.id) === Number(item.id)));
                } catch (e) {
                    this.crewResults = [];
                } finally {
                    this.crewLoading = false;
                }
            },
            addCrew(item) {
                if (this.selectedCrew.some(sel => Number(sel.id) === Number(item.id))) return;
                this.selectedCrew.push(item);
                this.crewQuery = '';
                this.crewResults = [];
            },
            removeCrew(id) {
                this.selectedCrew = this.selectedCrew.filter(item => Number(item.id) !== Number(id));
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
            <span class="text-gray-500 dark:text-gray-400">{{ __('Edit') }}</span>
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Task</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update task detail, crew, attachments, and comments.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm mb-1">Title</label>
                        <input name="title" value="{{ old('title', $task->title) }}" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Description</label>
                        <textarea name="description" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">{{ old('description', $task->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Status</label>
                        <select name="order_status_id" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                            <option value="">-</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" @selected(old('order_status_id', $task->order_status_id) == $status->id)>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-sm mb-1">Crew</label>
                        <input
                            type="text"
                            x-model="crewQuery"
                            @input.debounce.300ms="searchCrew()"
                            placeholder="Cari nama, NIK, HP, atau email user…"
                            class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700"
                        >
                        <div x-show="crewLoading" class="mt-2 text-xs text-gray-500">Searching...</div>
                        <div x-show="crewResults.length > 0" class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-56 overflow-auto">
                            <template x-for="item in crewResults" :key="item.id">
                                <button
                                    type="button"
                                    @click="addCrew(item)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                    x-text="item.name"
                                ></button>
                            </template>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="item in selectedCrew" :key="item.id">
                                <span class="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <span x-text="item.name"></span>
                                    <button type="button" @click="removeCrew(item.id)" class="text-blue-700 dark:text-blue-200">x</button>
                                    <input type="hidden" name="crew_ids[]" :value="item.id">
                                </span>
                            </template>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-button type="primary">Update</x-button>
                        <a href="{{ route('tasks.show', $task) }}"><x-button type="secondary">Back</x-button></a>
                    </div>
                </form>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Task Comments</h2>
                        @if(auth()->user()->hasPermission('create-task-comments'))
                            <button type="button" @click="showCommentModal = true" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">Add Comment</button>
                        @endif
                    </div>

                    @if(auth()->user()->hasPermission('view-task-comments') || auth()->user()->hasPermission('show-task-comments') || auth()->user()->hasPermission('show-tasks'))
                        <div class="space-y-2 max-h-[460px] overflow-y-auto pr-1">
                            @forelse($task->taskComments as $comment)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 text-sm bg-gray-50 dark:bg-gray-900/30">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="font-medium text-gray-800 dark:text-gray-100 text-sm truncate">{{ $comment->title ?: '-' }}</div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            @if($comment->attachment_type)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ ucfirst($comment->attachment_type) }}</span>
                                            @endif
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $comment->created_at?->format('d M Y H:i') ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                        By: {{ $comment->createdBy?->name ?? '-' }}
                                    </div>
                                    @if($comment->description)<div class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $comment->description }}</div>@endif
                                    @if($comment->attachment_type === 'photo' && $comment->file_path)
                                        <a class="text-blue-600 hover:underline block mt-1.5 text-xs" href="{{ asset('storage/'.$comment->file_path) }}" target="_blank">Open Photo</a>
                                        <img src="{{ asset('storage/'.$comment->file_path) }}" alt="Task comment photo" class="mt-1.5 max-h-24 rounded border border-gray-200 dark:border-gray-700">
                                    @elseif($comment->file_path)
                                        <a class="text-blue-600 hover:underline mt-1.5 inline-block text-xs" href="{{ asset('storage/'.$comment->file_path) }}" target="_blank">Open Attachment</a>
                                    @elseif($comment->link_url)
                                        <a class="text-blue-600 hover:underline mt-1.5 inline-block text-xs" href="{{ $comment->link_url }}" target="_blank">Open Link</a>
                                    @endif
                                    @if(auth()->user()->hasPermission('delete-task-comments'))
                                        <form method="POST" action="{{ route('tasks.comments.destroy', $comment) }}" class="mt-1.5">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-[11px]" onclick="return confirm('Delete comment?')">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">-</p>
                            @endforelse
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No permission.</p>
                    @endif
                </div>
            </div>

            <div class="xl:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Task Attachments</h2>
                        @if(auth()->user()->hasPermission('create-task-attachments'))
                            <button type="button" @click="showAttachmentModal = true" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">Add Attachment</button>
                        @endif
                    </div>

                    @if(auth()->user()->hasPermission('view-task-attachments') || auth()->user()->hasPermission('show-task-attachments') || auth()->user()->hasPermission('show-tasks'))
                        <div class="space-y-3">
                            @forelse($task->taskAttachments as $attachment)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-sm bg-gray-50 dark:bg-gray-900/30">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="font-medium text-gray-800 dark:text-gray-100">{{ $attachment->title ?: '-' }}</div>
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ ucfirst($attachment->attachment_type) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        By: {{ $attachment->createdBy?->name ?? '-' }}
                                    </div>
                                    @if($attachment->description)<div class="mt-1 text-gray-600 dark:text-gray-300">{{ $attachment->description }}</div>@endif
                                    @if($attachment->attachment_type === 'photo' && $attachment->file_path)
                                        <a class="text-blue-600 hover:underline block mt-2" href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank">Open Photo</a>
                                        <img src="{{ asset('storage/'.$attachment->file_path) }}" alt="Task attachment photo" class="mt-2 max-h-36 rounded border border-gray-200 dark:border-gray-700">
                                    @elseif($attachment->file_path)
                                        <a class="text-blue-600 hover:underline mt-2 inline-block" href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank">Open File</a>
                                    @elseif($attachment->link_url)
                                        <a class="text-blue-600 hover:underline mt-2 inline-block" href="{{ $attachment->link_url }}" target="_blank">Open Link</a>
                                    @endif
                                    @if(auth()->user()->hasPermission('delete-task-attachments'))
                                        <form method="POST" action="{{ route('tasks.attachments.destroy', $attachment) }}" class="mt-2">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete attachment?')">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">-</p>
                            @endforelse
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No permission.</p>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPermission('create-task-attachments'))
            <div x-show="showAttachmentModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="absolute inset-0 bg-black/50" @click="showAttachmentModal = false"></div>
                <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Add Attachment</h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" @click="showAttachmentModal = false">✕</button>
                    </div>
                    <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input name="title" placeholder="Title" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                        <textarea name="description" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700"></textarea>
                        <select name="attachment_type" x-model="attachmentType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                            <option value="photo">Photo</option>
                            <option value="file">File</option>
                            <option value="link">Link</option>
                        </select>
                        <div x-show="attachmentType === 'photo'">
                            <input type="file" name="photo" class="w-full text-sm">
                        </div>
                        <div x-show="attachmentType === 'file'">
                            <input type="file" name="file" class="w-full text-sm">
                        </div>
                        <div x-show="attachmentType === 'link'">
                            <input name="link_url" placeholder="https://..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showAttachmentModal = false" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-xs hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">Save Attachment</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if(auth()->user()->hasPermission('create-task-comments'))
            <div x-show="showCommentModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="absolute inset-0 bg-black/50" @click="showCommentModal = false"></div>
                <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Add Comment</h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" @click="showCommentModal = false">✕</button>
                    </div>
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input name="title" placeholder="Title" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                        <textarea name="description" placeholder="Description" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700"></textarea>
                        <select name="attachment_type" x-model="commentType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                            <option value="">No attachment</option>
                            <option value="photo">Photo</option>
                            <option value="file">File</option>
                            <option value="link">Link</option>
                        </select>
                        <div x-show="commentType === 'photo'">
                            <input type="file" name="photo" class="w-full text-sm">
                        </div>
                        <div x-show="commentType === 'file'">
                            <input type="file" name="file" class="w-full text-sm">
                        </div>
                        <div x-show="commentType === 'link'">
                            <input name="link_url" placeholder="https://..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700">
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showCommentModal = false" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-xs hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">Save Comment</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>

