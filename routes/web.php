<?php

use App\Http\Controllers\ChangeLogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'today'])->name('dashboard');
    Route::get('/today', [DashboardController::class, 'today'])->name('today');
    Route::get('/overdue', [DashboardController::class, 'overdue'])->name('overdue');
    Route::get('/inbox', [DashboardController::class, 'inbox'])->name('inbox');
    Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
    Route::get('/day', [DashboardController::class, 'day'])->name('day');

    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/update-field', [TaskController::class, 'updateField'])->name('tasks.updateField');

    Route::resource('projects', ProjectController::class);
    Route::resource('tags', TagController::class);

    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('attachments.download');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/changelogs/task/{task}', [ChangeLogController::class, 'task'])->name('changelogs.task');
    Route::get('/changelogs/project/{project}', [ChangeLogController::class, 'project'])->name('changelogs.project');
    Route::get('/changelogs/tag/{tag}', [ChangeLogController::class, 'tag'])->name('changelogs.tag');
    Route::get('/changelogs/user', [ChangeLogController::class, 'user'])->name('changelogs.user');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
