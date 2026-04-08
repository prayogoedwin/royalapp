<x-layouts.app>
    <div x-data="{ showAttachmentModal: false, showCommentModal: false, attachmentType: 'photo', commentType: '' }">
        <div class="mb-6 flex items-center text-sm">
            <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('tasks.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Tasks') }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-500 dark:text-gray-400">{{ __('Detail') }}</span>
        </div>

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $task->title }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $task->description ?: '-' }}</p>
            </div>
            <div class="flex gap-2">
                @if(auth()->user()->hasPermission('edit-tasks'))
                    <a href="{{ route('tasks.edit', $task) }}"><x-button type="primary">Edit</x-button></a>
                @endif
                <a href="{{ route('tasks.index') }}"><x-button type="secondary">Back</x-button></a>
            </div>
        </div>

        @php
            $statusColor = match($task->orderStatus?->color) {
                'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            };
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                    <h2 class="font-semibold mb-3 text-gray-800 dark:text-gray-100">Task Info</h2>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ $task->orderStatus?->name ?? '-' }}
                        </span>
                    </div>
                    <div class="text-sm mt-2">
                        <span class="text-gray-500 dark:text-gray-400">Created By:</span>
                        <span class="text-gray-800 dark:text-gray-100">{{ $task->createdBy?->name ?? '-' }}</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-100">Crew</h3>
                        <ul class="list-disc pl-5 text-sm space-y-1 text-gray-700 dark:text-gray-300">
                            @forelse($task->taskCrews as $crew)
                                <li>{{ $crew->employee?->full_name ?? '-' }}{{ $crew->role ? ' - '.$crew->role : '' }}</li>
                            @empty
                                <li class="text-gray-500 dark:text-gray-400">-</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

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

