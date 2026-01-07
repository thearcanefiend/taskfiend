<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'creator_id',
        'date',
        'time',
        'project_id',
        'recurrence_pattern',
    ];

    protected $attributes = [
        'status' => 'incomplete',
    ];

    protected $casts = [
        // Removed 'date' cast - we store it as a plain string in YYYY-MM-DD format
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_task');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignments', 'task_id', 'assignee_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ChangeLog::class, 'entity_id')
            ->where('entity_type', 'tasks');
    }
}
