<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Services\DateParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()
            ->where(function ($q) {
                $q->where('creator_id', Auth::id())
                  ->orWhereHas('assignees', function ($query) {
                      $query->where('users.id', Auth::id());
                  });
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'archived')
                  ->where('status', '!=', 'done');
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->with(['creator', 'project', 'tags', 'assignees', 'attachments', 'comments'])
            ->orderBy('date')
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    public function create(Request $request)
    {
        $projects = Project::where('user_id', Auth::id())
            ->orWhereHas('tasks.assignees', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->where('status', '!=', 'archived')
            ->get();

        $tags = Tag::orderBy('tag_name')->get();
        $users = User::whereNull('email_enabled_at')->get();

        $preselectedProjectId = $request->query('project_id');
        $preselectedDate = $request->query('date');

        return view('tasks.create', compact('projects', 'tags', 'users', 'preselectedProjectId', 'preselectedDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i',
            'project_id' => 'nullable|exists:projects,id',
            'recurrence_pattern' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $taskName = $validated['name'];
        $date = $validated['date'] ?? null;
        $time = $validated['time'] ?? null;
        $recurrencePattern = $validated['recurrence_pattern'] ?? null;

        // Auto-parse date and recurrence from task name if not explicitly provided
        if (!$date && !$recurrencePattern) {
            $dateParser = new DateParser();
            $parsed = $dateParser->parseTaskInput($taskName);
            $taskName = $parsed['name'];
            $date = $parsed['date'];
            $time = $parsed['time'];
            $recurrencePattern = $parsed['recurrence_pattern'];
        }

        // Auto-populate date from recurrence pattern if recurrence is set but date is not
        if ($recurrencePattern && !$date) {
            $dateParser = new DateParser();
            $nextOccurrence = $dateParser->getNextOccurrence($recurrencePattern, now());
            if ($nextOccurrence) {
                $date = $nextOccurrence->format('Y-m-d');
            }
        }

        $task = Task::create([
            'name' => $taskName,
            'description' => $validated['description'] ?? null,
            'date' => $date,
            'time' => $time,
            'project_id' => $validated['project_id'] ?? null,
            'recurrence_pattern' => $recurrencePattern,
            'creator_id' => Auth::id(),
            'status' => 'incomplete',
        ]);

        if (isset($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        $assigneeIds = $validated['assignee_ids'] ?? [Auth::id()];
        foreach ($assigneeIds as $assigneeId) {
            $task->assignments()->create([
                'assignee_id' => $assigneeId,
                'assigned_by_id' => Auth::id(),
            ]);
        }

        $this->logChange($task, 'created task');

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load(['creator', 'project', 'tags', 'assignees', 'assignments.assignedBy',
                     'attachments', 'comments.user', 'changeLogs.user']);

        $projects = Project::where('user_id', Auth::id())
            ->orWhereHas('tasks.assignees', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->where('status', '!=', 'archived')
            ->get();

        $tags = Tag::orderBy('tag_name')->get();
        $users = User::whereNull('email_enabled_at')->get();

        return view('tasks.show', compact('task', 'projects', 'tags', 'users'));
    }

    public function edit(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $projects = Project::where('user_id', Auth::id())
            ->orWhereHas('tasks.assignees', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->where('status', '!=', 'archived')
            ->get();

        $tags = Tag::orderBy('tag_name')->get();
        $users = User::whereNull('email_enabled_at')->get();

        return view('tasks.edit', compact('task', 'projects', 'tags', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i',
            'project_id' => 'nullable|exists:projects,id',
            'recurrence_pattern' => 'nullable|string',
            'status' => 'in:incomplete,done,archived',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $changes = [];
        foreach (['name', 'description', 'date', 'time', 'project_id', 'recurrence_pattern', 'status'] as $field) {
            if (isset($validated[$field]) && $task->$field != $validated[$field]) {
                $changes[$field] = ['old' => $task->$field, 'new' => $validated[$field]];
                $task->$field = $validated[$field];
            }
        }

        $task->save();

        if (isset($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        if (isset($validated['assignee_ids']) && $task->creator_id === Auth::id()) {
            $currentAssigneeIds = $task->assignments->pluck('assignee_id')->toArray();
            $newAssigneeIds = $validated['assignee_ids'];

            $toRemove = array_diff($currentAssigneeIds, $newAssigneeIds);
            $toAdd = array_diff($newAssigneeIds, $currentAssigneeIds);

            $task->assignments()->whereIn('assignee_id', $toRemove)->delete();

            foreach ($toAdd as $assigneeId) {
                $task->assignments()->create([
                    'assignee_id' => $assigneeId,
                    'assigned_by_id' => Auth::id(),
                ]);
            }
        }

        foreach ($changes as $field => $change) {
            $this->logChange($task, "changed {$field} from {$change['old']} to {$change['new']}");
        }

        if ($task->status === 'done' && $task->recurrence_pattern) {
            $this->createRecurringTask($task);
        }

        // Handle quick complete from task list
        if ($request->has('quick_complete')) {
            return redirect()->back()
                ->with('success', 'Task marked as done.');
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function updateField(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $field = $request->input('field');
        $allowedFields = ['name', 'description', 'status', 'date', 'time', 'project_id', 'recurrence_pattern', 'tag_ids', 'assignee_ids'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
        }

        try {
            if ($field === 'tag_ids') {
                $tagIds = $request->input('tag_ids', []);
                $task->tags()->sync($tagIds);
                $this->logChange($task, 'updated tags');
            } elseif ($field === 'assignee_ids') {
                if ($task->creator_id !== Auth::id()) {
                    return response()->json(['success' => false, 'message' => 'Only creator can change assignees'], 403);
                }

                $assigneeIds = $request->input('assignee_ids', []);
                $currentAssigneeIds = $task->assignments->pluck('assignee_id')->toArray();

                $toRemove = array_diff($currentAssigneeIds, $assigneeIds);
                $toAdd = array_diff($assigneeIds, $currentAssigneeIds);

                $task->assignments()->whereIn('assignee_id', $toRemove)->delete();

                foreach ($toAdd as $assigneeId) {
                    $task->assignments()->create([
                        'assignee_id' => $assigneeId,
                        'assigned_by_id' => Auth::id(),
                    ]);
                }

                $this->logChange($task, 'updated assignees');
            } else {
                $value = $request->input('value');

                if ($field === 'status') {
                    if (!in_array($value, ['incomplete', 'done', 'archived'])) {
                        return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
                    }
                }

                $task->$field = $value;
                $task->save();

                $this->logChange($task, "updated {$field}");

                if ($field === 'status' && $value === 'done' && $task->recurrence_pattern) {
                    $this->createRecurringTask($task);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Task $task)
    {
        abort(403, 'Tasks cannot be deleted. Please archive instead.');
    }

    protected function authorizeTaskAccess(Task $task)
    {
        $isCreator = $task->creator_id === Auth::id();
        $isAssignee = $task->assignees->contains('id', Auth::id());

        if (!$isCreator && !$isAssignee) {
            abort(403, 'You do not have access to this task.');
        }
    }

    protected function logChange(Task $task, string $description)
    {
        $task->changeLogs()->create([
            'date' => now(),
            'user_id' => Auth::id(),
            'entity_type' => 'tasks',
            'entity_id' => $task->id,
            'description' => Auth::user()->name . ' ' . $description,
        ]);
    }

    protected function createRecurringTask(Task $originalTask)
    {
        if (!$originalTask->recurrence_pattern) {
            return;
        }

        $dateParser = new DateParser();
        $baseDate = $originalTask->date ? new \DateTime($originalTask->date) : now();
        $nextOccurrence = $dateParser->getNextOccurrence(
            $originalTask->recurrence_pattern,
            $baseDate
        );

        if (!$nextOccurrence) {
            return;
        }

        $nextDate = $nextOccurrence->format('Y-m-d');

        $existingTask = Task::where('creator_id', $originalTask->creator_id)
            ->where('name', $originalTask->name)
            ->where('recurrence_pattern', $originalTask->recurrence_pattern)
            ->where('status', 'incomplete')
            ->where('date', $nextDate)
            ->first();

        if ($existingTask) {
            return;
        }

        $newTask = Task::create([
            'name' => $originalTask->name,
            'description' => $originalTask->description,
            'date' => $nextDate,
            'time' => $originalTask->time,
            'project_id' => $originalTask->project_id,
            'recurrence_pattern' => $originalTask->recurrence_pattern,
            'creator_id' => $originalTask->creator_id,
            'status' => 'incomplete',
        ]);

        $newTask->tags()->sync($originalTask->tags->pluck('id'));

        foreach ($originalTask->assignments as $assignment) {
            $newTask->assignments()->create([
                'assignee_id' => $assignment->assignee_id,
                'assigned_by_id' => $assignment->assigned_by_id,
            ]);
        }

        foreach ($originalTask->attachments as $attachment) {
            $newTask->attachments()->create([
                'user_id' => $attachment->user_id,
                'file_path' => $attachment->file_path,
                'original_filename' => $attachment->original_filename,
                'mime_type' => $attachment->mime_type,
                'file_size' => $attachment->file_size,
            ]);
        }

        $newTask->changeLogs()->create([
            'date' => now(),
            'user_id' => Auth::id(),
            'entity_type' => 'tasks',
            'entity_id' => $newTask->id,
            'description' => Auth::user()->name . ' created recurring task',
        ]);
    }
}
