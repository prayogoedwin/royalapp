<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OrderStatus;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TaskCrew;
use App\Support\UploadPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tasks = Task::with(['orderStatus', 'createdBy'])->withCount('taskCrews')->select('tasks.*');

            return DataTables::of($tasks)
                ->addColumn('status_badge', function (Task $task) {
                    $name = $task->orderStatus?->name ?? '-';
                    $colors = [
                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                    ];
                    $color = $colors[$task->orderStatus?->color ?? 'gray'] ?? $colors['gray'];
                    return '<span class="px-2 py-1 rounded-full text-xs font-medium ' . $color . '">' . e($name) . '</span>';
                })
                ->addColumn('crew_count', fn (Task $task) => (string) ($task->task_crews_count ?? 0))
                ->addColumn('created_by_name', fn (Task $task) => e($task->createdBy?->name ?? '-'))
                ->addColumn('actions', function (Task $task) {
                    $actions = '';
                    if (auth()->user()->hasPermission('show-tasks')) {
                        $actions .= '<a href="' . route('tasks.show', $task) . '" class="text-green-600 dark:text-green-400 hover:underline mr-3">View</a>';
                    }
                    if (auth()->user()->hasPermission('edit-tasks')) {
                        $actions .= '<a href="' . route('tasks.edit', $task) . '" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</a>';
                    }
                    if (auth()->user()->hasPermission('delete-tasks')) {
                        $actions .= '<form action="' . route('tasks.destroy', $task) . '" method="POST" class="inline" onsubmit="return confirm(\'Delete task?\')">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button></form>';
                    }
                    return $actions ?: '-';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('tasks.index');
    }

    public function create(): View
    {
        $statuses = OrderStatus::orderBy('name')->get();
        $initialCrew = collect();

        $oldCrewIds = collect(old('crew_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($oldCrewIds->isNotEmpty()) {
            $initialCrew = Employee::with('position')
                ->whereIn('id', $oldCrewIds)
                ->orderBy('full_name')
                ->get()
                ->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'name' => trim($employee->full_name . ($employee->position?->nama ? ' - ' . $employee->position->nama : '')),
                ]);
        }

        return view('tasks.create', compact('statuses', 'initialCrew'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order_status_id' => ['nullable', 'exists:order_statuses,id'],
            'crew_ids' => ['nullable', 'array'],
            'crew_ids.*' => ['exists:employees,id'],
        ]);

        DB::beginTransaction();
        try {
            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'order_status_id' => $validated['order_status_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach (($validated['crew_ids'] ?? []) as $employeeId) {
                $employee = Employee::with('position')->find($employeeId);
                TaskCrew::create([
                    'task_id' => $task->id,
                    'employee_id' => $employeeId,
                    'role' => $employee?->position?->nama,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('tasks.show', $task)->with('status', 'Task created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create task: ' . $e->getMessage()]);
        }
    }

    public function show(Task $task): View
    {
        $task->load([
            'orderStatus',
            'taskCrews.employee.position',
            'taskAttachments.createdBy',
            'taskComments.createdBy',
            'createdBy',
        ]);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        $task->load([
            'taskCrews.employee.position',
            'taskAttachments.createdBy',
            'taskComments.createdBy',
            'createdBy',
            'orderStatus',
        ]);
        $statuses = OrderStatus::orderBy('name')->get();
        $oldCrewIds = collect(old('crew_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedCrewIds = $oldCrewIds->isNotEmpty()
            ? $oldCrewIds
            : $task->taskCrews->pluck('employee_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();

        $initialCrew = collect();
        if ($selectedCrewIds->isNotEmpty()) {
            $initialCrew = Employee::with('position')
                ->whereIn('id', $selectedCrewIds)
                ->orderBy('full_name')
                ->get()
                ->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'name' => trim($employee->full_name . ($employee->position?->nama ? ' - ' . $employee->position->nama : '')),
                ]);
        }

        return view('tasks.edit', compact('task', 'statuses', 'initialCrew'));
    }

    public function searchCrew(Request $request)
    {
        $keyword = trim((string) $request->get('q', ''));

        // Crew disimpan sebagai employees.id (task_crews.employee_id), bukan users.id.
        $query = Employee::query()
            ->with(['position', 'user'])
            ->where('status', 'active')
            ->orderBy('full_name');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('nik', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $items = $query->limit(20)->get()->map(fn (Employee $employee) => [
            'id' => $employee->id,
            'name' => trim($employee->full_name . ($employee->position?->nama ? ' - ' . $employee->position->nama : '')),
        ]);

        return response()->json($items);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order_status_id' => ['nullable', 'exists:order_statuses,id'],
            'crew_ids' => ['nullable', 'array'],
            'crew_ids.*' => ['exists:employees,id'],
        ]);

        DB::beginTransaction();
        try {
            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'order_status_id' => $validated['order_status_id'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            $task->taskCrews()->forceDelete();
            foreach (($validated['crew_ids'] ?? []) as $employeeId) {
                $employee = Employee::with('position')->find($employeeId);
                TaskCrew::create([
                    'task_id' => $task->id,
                    'employee_id' => $employeeId,
                    'role' => $employee?->position?->nama,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('tasks.show', $task)->with('status', 'Task updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update task: ' . $e->getMessage()]);
        }
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->update(['deleted_by' => auth()->id()]);
        $task->delete();
        return redirect()->route('tasks.index')->with('status', 'Task deleted successfully.');
    }

    public function storeAttachment(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'attachment_type' => ['required', 'in:photo,file,link'],
            'file' => ['nullable', 'file', 'max:5120'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'link_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $filePath = null;
        $linkUrl = null;

        if ($validated['attachment_type'] === 'photo') {
            if (! $request->hasFile('photo')) {
                return back()->withErrors(['error' => 'Photo is required.']);
            }
            $filePath = $request->file('photo')->store(UploadPath::dir('task-attachments'), 'public');
        } elseif ($validated['attachment_type'] === 'file') {
            if (! $request->hasFile('file')) {
                return back()->withErrors(['error' => 'File is required.']);
            }
            $filePath = $request->file('file')->store(UploadPath::dir('task-attachments'), 'public');
        } else {
            if (empty($validated['link_url'])) {
                return back()->withErrors(['error' => 'Link URL is required.']);
            }
            $linkUrl = $validated['link_url'];
        }

        TaskAttachment::create([
            'task_id' => $task->id,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'attachment_type' => $validated['attachment_type'],
            'file_path' => $filePath,
            'link_url' => $linkUrl,
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', 'Task attachment added.');
    }

    public function destroyAttachment(TaskAttachment $taskAttachment): RedirectResponse
    {
        if ($taskAttachment->file_path) {
            Storage::disk('public')->delete($taskAttachment->file_path);
        }
        $taskAttachment->delete();
        return back()->with('status', 'Task attachment deleted.');
    }

    public function storeComment(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'attachment_type' => ['nullable', 'in:photo,file,link'],
            'file' => ['nullable', 'file', 'max:5120'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'link_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $filePath = null;
        $linkUrl = null;
        $type = $validated['attachment_type'] ?? null;

        if ($type === 'photo') {
            if (! $request->hasFile('photo')) {
                return back()->withErrors(['error' => 'Photo is required for comment photo type.']);
            }
            $filePath = $request->file('photo')->store(UploadPath::dir('task-comments'), 'public');
        } elseif ($type === 'file') {
            if (! $request->hasFile('file')) {
                return back()->withErrors(['error' => 'File is required for comment file type.']);
            }
            $filePath = $request->file('file')->store(UploadPath::dir('task-comments'), 'public');
        } elseif ($type === 'link') {
            if (empty($validated['link_url'])) {
                return back()->withErrors(['error' => 'Link URL is required for comment link type.']);
            }
            $linkUrl = $validated['link_url'];
        }

        TaskComment::create([
            'task_id' => $task->id,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'attachment_type' => $type,
            'file_path' => $filePath,
            'link_url' => $linkUrl,
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', 'Task comment added.');
    }

    public function destroyComment(TaskComment $taskComment): RedirectResponse
    {
        if ($taskComment->file_path) {
            Storage::disk('public')->delete($taskComment->file_path);
        }
        $taskComment->delete();
        return back()->with('status', 'Task comment deleted.');
    }
}

