<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status',
        'is_inbox',
    ];

    protected $attributes = [
        'status' => 'incomplete',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ChangeLog::class, 'entity_id')
            ->where('entity_type', 'projects');
    }
}
