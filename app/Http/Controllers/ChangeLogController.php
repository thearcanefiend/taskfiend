<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use App\Models\Task;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeLogController extends Controller
{
    public function task(Task $task)
    {
        $isCreator = $task->creator_id === Auth::id();
        $isAssignee = $task->assignees->contains('id', Auth::id());

        if (!$isCreator && !$isAssignee) {
            abort(403, 'You do not have access to this task.');
        }

        $changeLogs = ChangeLog::where('entity_type', 'tasks')
            ->where('entity_id', $task->id)
            ->with('user')
            ->orderByDesc('date')
            ->get();

        return view('changelogs.index', compact('changeLogs', 'task'));
    }

    public function project(Project $project)
    {
        $isCreator = $project->user_id === Auth::id();
        $hasTaskInProject = $project->tasks()
            ->whereHas('assignees', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->exists();

        if (!$isCreator && !$hasTaskInProject) {
            abort(403, 'You do not have access to this project.');
        }

        $changeLogs = ChangeLog::where(function ($q) use ($project) {
            $q->where(function ($subQ) use ($project) {
                $subQ->where('entity_type', 'projects')
                     ->where('entity_id', $project->id);
            })
            ->orWhere(function ($subQ) use ($project) {
                $taskIds = $project->tasks()->pluck('id');
                $subQ->where('entity_type', 'tasks')
                     ->whereIn('entity_id', $taskIds);
            });
        })
        ->with('user')
        ->orderByDesc('date')
        ->get();

        return view('changelogs.index', compact('changeLogs', 'project'));
    }

    public function tag(Tag $tag)
    {
        $changeLogs = ChangeLog::where(function ($q) use ($tag) {
            $q->where(function ($subQ) use ($tag) {
                $subQ->where('entity_type', 'tags')
                     ->where('entity_id', $tag->id);
            })
            ->orWhere(function ($subQ) use ($tag) {
                $taskIds = $tag->tasks()
                    ->where(function ($q) {
                        $q->where('creator_id', Auth::id())
                          ->orWhereHas('assignees', function ($query) {
                              $query->where('users.id', Auth::id());
                          });
                    })
                    ->pluck('id');
                $subQ->where('entity_type', 'tasks')
                     ->whereIn('entity_id', $taskIds);
            });
        })
        ->with('user')
        ->orderByDesc('date')
        ->get();

        return view('changelogs.index', compact('changeLogs', 'tag'));
    }

    public function user()
    {
        $changeLogs = ChangeLog::where('user_id', Auth::id())
            ->with('user')
            ->orderByDesc('date')
            ->get();

        // Load related entities for display
        foreach ($changeLogs as $log) {
            if ($log->entity_type === 'tasks' && $log->entity_id) {
                $log->entity = Task::find($log->entity_id);
            } elseif ($log->entity_type === 'projects' && $log->entity_id) {
                $log->entity = Project::find($log->entity_id);
            } elseif ($log->entity_type === 'tags' && $log->entity_id) {
                $log->entity = Tag::find($log->entity_id);
            }
        }

        return view('changelogs.index', compact('changeLogs'));
    }
}
